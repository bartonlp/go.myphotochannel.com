#! /usr/bin/php6 -q
<?php
// BLP 2014-01-10 -- New approach. Just add photo to data base rename it and move it to content.
// Then later go back and resize any of the unprocessed photos. I'll add a field to the items table
// to indicate if the image is resized or not.
// BLP 2014-01-09 -- Rework to use curl to send resize part to Apache   
// Gather Photos Emailed to the Server by Customers
// This is a CLI program run by CRON every minute.

#$debug = true;

// Look to see if we are already running

$str = exec("ps |grep 'emailphoto.php'|wc -l");
if($str > 1) {
  echo "emailphoto.php already running. Done\n";
  exit();
}
$starttime = time();

// Also force our TOPFILE
define('TOPFILE', "/homepages/45/d454707514/htdocs/siteautoload.php");
// Now this looks like all the other files.
if(file_exists(TOPFILE)) {
  include(TOPFILE);
} else {
  echo "Can't find siteautoload.php";
  exit();
}

// During debug
Error::setDevelopment(true);
Error::setNoEmailErrs(true);
Error::setNoHtml(true);

$S = new Database($dbinfo);

function getversion($path) {
  $name = realpath("$path");
  $version = preg_replace('/^.*?(v\d+\.\d+).*$/', "$1", $name);
  return $version;
}

$version = getversion(getcwd());

$app_id = '52258';
$key = '2aa0c68479472ef92d2a';
$secret = '86714601dfa6e13a87f7';
$pusher = new Pusher($key, $secret, $app_id);

$sql = "select siteId, emailServer, emailUsername, emailPassword, emailPort from sites";
try {
  $S->query($sql);
} catch(Exception $e) {
  unset($S);
  $S = new Database($GLOBALS['dbinfo']);
  echo "RETRY: $sql\n";
  try {
    $S->query($sql); // try same sql again
  } catch(Exception $e) {
    echo "Tried retry unset and new Database but still got error. Error: ".$e->getCode()."\n";
    exit();
  }
}

$result = $S->getResult(); // get result because we do further query commands in loop.

// For each site

//echo "SITE_ROOT: ".SITE_ROOT."\n";
$totalphotos = 0;

while(list($siteId, $host, $user, $password, $port) = $S->fetchrow($result, 'num')) {
  // escape siteId and save it in $S for functions and $siteId for main flow.
  // We need $siteId so if we have to re new Database() we don't lose $S->siteId!
  $S->siteId = $siteId = $S->escape($siteId);

  // Do the items table check

  try {
    $sql = "select * from modified where siteId='$siteId' and xchange=1";
    $n = $S->query($sql);
  } catch(Exception $e) {
    unset($S);
    $S = new Database($GLOBALS['dbinfo']);
    $S->siteId = $siteId;
    echo "RETRY: $sql\n";

    try {
      $n = $S->query($sql); // try same sql again
    } catch(Exception $e) {
      echo "Tried retry unset and new Database but still got error. Error: ".$e->getCode()."\n";
      exit();
    }
  }
  
  if($n) {
    // update it to zero again
    try {
      $sql = "update modified set xchange=0 where siteId='$siteId'";
      $S->query($sql);
    } catch(Exception $e) {
      unset($S);
      $S = new Database($GLOBALS['dbinfo']);
      $S->siteId = $siteId;
      echo "RETRY: $sql\n";

      try {
        $S->query($sql); // try same sql again
      } catch(Exception $e) {
        echo "Tried retry unset and new Database but still got error. Error: ".$e->getCode()."\n";
        exit();
      }
    }

    // Now tell the slideshows to do a fastCall because something in items, appinfo,
    // categories, segments or sites has changed.

    $pusher->trigger("slideshow", "fastcall", array('siteId'=>$siteId));
    date_default_timezone_set("America/Denver");
    echo date("Y-m-d H:i T") . ", FastCall for $siteId: ". "\n--------------------------\n";
  }
  
  $S->msg = ''; // new msg for new site
  $photonum = 0;
  // Open the imap server for this site

  $mbox = @imap_open("{{$host}/imap/notls:{$port}}INBOX", "$user", "$password");

  if(!$mbox) {
    echo "\nError opening mail box for $user at $host with password=$password\n";
    continue;
  }

  // Look for messages

  $check = imap_mailboxmsginfo($mbox);

  if($check->Nmsgs) {
    for($i=1; $i < $check->Nmsgs+1; ++$i) {
      $header = imap_headerinfo($mbox, $i);
      
      // hasImage() returns false if no image in the message.
      // If image or images are found hasImage() returns an array with part and filename for each
      // image found.

      if(($v = hasImage($mbox, $i))) {
        $from = $header->fromaddress;
        if(preg_match("/\?utf-8\?B\?(.*?)\?=/", $from, $m)) {
          $from = base64_decode($m[1]);
        }
        $S->subject = $header->subject;
        $S->from = $S->escape(escapeltgt($from));

        // $v is a numeric array of numeric arrays with [0]=part, [1]=filename
        date_default_timezone_set("America/Denver");
        echo  date("Y-m-d H:i T") . ", $version, Nmsgs: " . ($check->Nmsgs) .
            ", Parts: " . count($v) . ", " . "\n";

        $msgBody = rtrim(get_part($mbox, $i, "TEXT/PLAIN"));

        if(!empty($msgBody)) {
          // Remove blank lines
          $msgBody = preg_replace("/\n+\s*\n/", "\n", $msgBody);
          $msgBody .= "\n";
        }
        
        echo "from:$from, subject: $S->subject\n$msgBody+++++++++++++++++++++++++\n";
        
        foreach($v as $f) {
          // $f[0]=part, $f[1]=filename
            
          $part = imap_fetchbody($mbox, $i, $f[0]);
          
          $S->image = base64_decode($part);
          $S->ext = strtolower(pathinfo($f[1], PATHINFO_EXTENSION));
          
          $S = fixupNewPhotos($S);

          ++$photonum;
          ++$totalphotos;
          
          unset($image, $part, $from, $subject, $msgBody);
        }
        // Mark the email for deletion

        imap_delete($mbox, $i);
      } else {
        $msgBody = rtrim(get_part($mbox, $i, "TEXT/PLAIN"));
        if(!empty($msgBody)) {
          $msgBody .= "\n";
        }
        echo "NO IMAGE: from: $header->fromaddress, subject: $header->subject\n$msgBody--------------------------\n";
        unset($msgBody);
        
        imap_delete($mbox, $i);
        continue;
      }
    }

    if($photonum) {
      // Now send the email to the site admin

      $msg = <<<EOF
Approve photos at:
http://go.myphotochannel.com/cpanel/cpanel.php?siteId=$siteId
EOF;

      $hdrs = "From: ".EMAILALERTS."\r\n";

      try {
        $sql = "select id, email, notifyPhone, notifyCarrier, emailNotify, textNotify from users ".
               "where siteId='$siteId'";
        $n = $S->query($sql);
      } catch(Exception $e) {
        unset($S);
        $S = new Database($GLOBALS['dbinfo']);
        $S->siteId = $siteId;
        echo "RETRY: users\n";

        try {
          $n = $S->query($sql); // try same sql again
        } catch(Exception $e) {
          echo "Tried retry unset and new Database but still got error. Error: ".$e->getCode()."\n";
          exit();
        }
      }

      if(!$n) {
        $msg = "'emailphoto.php': Did not find an entry in users table ".
               "with emailNotify='yes' and siteId='$siteId'\n";

        echo "$msg\n$sql\n";
        mail(EMAILADDRESS, "ERROR: emailphoto.php", $msg, $hdrs, "-f".EMAILRETURN);
      } else {
        // Send a message to each admin

        while(list($userId, $email, $phone, $carrier, $emailNotify, $textNotify) = $S->fetchrow('num')) {
          $S->msg = $msg . "&userId=$userId";
          if($emailNotify == 'yes') {
            echo "Email to: $email\n";
            mail($email, "New Photos", $S->msg, $hdrs, "-f".EMAILRETURN);
          }
          if($textNotify == 'yes' && $phone && $carrier) {
            echo "Text to: $phone, $carrier\n";
            sendText($phone, $carrier, $S->msg);
          }
        }
      }
      echo "Done $siteId\n--------------------------\n";
    }
  } // end of if(check->NMsgs)

  // Close the imap for the site and loop to next site

  imap_close($mbox, CL_EXPUNGE); // remove any deleted messages
}

date_default_timezone_set("America/Denver");
$d = date("Y-m-d H:i T");
$time = time() - $starttime;

if($totalphotos) {
  echo "$d ALL DONE: Processed $totalphotos photos.\n".
       "Elapsed time $time sec. Version $version\n==========================\n";  
} elseif(preg_match("/:00 /", $d)) {
  echo "$d, Mark $version\n==========================\n";
}

exit();

// HELPER FUNCTION

function fixupNewPhotos($S) {
  // Put $S->siteId into $siteId so if we have to re new Database() we don't lose siteId!
  $siteId = $S->siteId;
  
  $cat = 'photo';

  if($S->subject == "Image announcement") {
    $cat = 'announce';
  }

  // Get the last itemId in the items table.

  try {
    $sql = "select max(itemId) from items";
    $n = $S->query($sql);
  } catch(Exception $e) {
    echo "Error: ".$e->getCode()."\n";
    exit();
  }

  if(!$n) {
    echo "Error: ".$e->getCode()."\n";
    exit();
  }
  
  list($newid) = $S->fetchrow('num');
  ++$newid;

  try {
    $sql = "insert into items (siteId, itemId, category, showTime, ".
           "creatorName, description, status, location, resized) ".
           "values('$siteId', '$newid', '$cat', now(), ".
           "'$S->from', '$newid.jpg', 'new', 'content/$newid.$S->ext', 'no')";

    //echo "sql: $sql";
    $S->query($sql);
  } catch(Exception $e) {
    echo $e;
    exit();
  }

  $S->msg .= "$newid.$S->ext\n";
  $S->newfile = "$newid.$S->ext";

  // Now just put this fullsized image in the content directory.
  // We will resize it later.

  echo "Filename: ".SITE_ROOT ."/content/$S->newfile\n";
  file_put_contents(SITE_ROOT ."/content/$S->newfile", $S->image);

  return $S;
}

// Send text message to phone, at carrier

function sendText($phone, $carrier, $msg) {
  switch(strtolower($carrier)) {
    case "verizon":
      $carrier = "@vtext.com";
      break;
    case "tmobile":
      $carrier = "@tomomail.net";
      break;
    case "sprint":
      $carrier = "@messaging.sprintpcs.com";
      break;
    case "att":
      $carrier = "@txt.att.net";
      break;
    case "virgin":
      $carrier = "@vmobl.com";
      break;
  }
  mail("{$phone}{$carrier}", "SMS", $msg, "From: ".EMAILALERTS."\r\n");
}

// Does the email have an image attached?
// returns an array for each image that has [0]=index (part), [1]=filename

function hasImage($stream, $msg_number, $structure=false) {
  if(!$structure) {
    $structure = imap_fetchstructure($stream, $msg_number);
    //print_r($structure);
    $ret = null;
  }
  if($structure) {
    if($structure->type == 5) {
      $x = $structure->parameters[0];
      //var_dump($structure);
      //var_dump($x);
      return $x->value;
    } elseif(($structure->type == 3) && ($structure->subtype == "OCTET-STREAM")) {
      $x = $structure->dparameters[0];
      return $x->value;
    } elseif($structure->type == 1) {
      while(list($index, $sub_structure) = each($structure->parts)) {
        $x = hasImage($stream, $msg_number, $sub_structure);
        if($x) $ret[] = array($index+1, $x);
      }
    }
  }
  if(!is_null($ret)) return $ret;
  return false;
}

// HELPER FUNCTION

function get_mime_type(&$structure) {
  $primary_mime_type = array("TEXT", "MULTIPART","MESSAGE", "APPLICATION", "AUDIO","IMAGE", "VIDEO", "OTHER");
  if($structure->subtype) {
    return $primary_mime_type[(int) $structure->type] . '/' .$structure->subtype;
  }
  return "TEXT/PLAIN";
}

// The function get_part() needs 3 parameters.
// 1. Mailbox connection (e.g. $mbox from my connection example)
// 2. Message number to look up (e.g. $msg from my message list example)
// 3. A content type to check for

function get_part($stream, $msg_number, $mime_type, $structure = false, $part_number = false) {
  if(!$structure) {
    $structure = imap_fetchstructure($stream, $msg_number);
  }

  if($structure) {
    if($mime_type == get_mime_type($structure)) {
   	  if(!$part_number) {
   		  $part_number = "1";
   		}
   		$text = imap_fetchbody($stream, $msg_number, $part_number);

   		if($structure->encoding == 3) {
   		  return imap_base64($text);
   		} else if($structure->encoding == 4) {
        return imap_qprint($text);
   		} else {
   		  return $text;
   		}
   	}

		if($structure->type == 1) /* multipart */ {
   	  while(list($index, $sub_structure) = each($structure->parts)) {
   		  if($part_number) {
   			  $prefix = $part_number . '.';
   			}
   			$data = get_part($stream, $msg_number, $mime_type, $sub_structure, $prefix . ($index + 1));

        if($data) {
   				return $data;
   			}
   		} // END OF WHILE
    } // END OF MULTIPART
  } // END OF STRUTURE
  return false;
}

?>

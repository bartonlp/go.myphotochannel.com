#! /bin/bash
# Make a new version.
# Copy working version directory to new directory that is named
# vN.(nn+1). For example if the working version directory is v1.07 the
# new working version directory will be v1.08. We would copy everything
# recursively from v1.07 to a new v1.08 directory.
# We remove the old symlinks for oldVersion, currentVersion and
# workingVersion and create new symlink. For example if our directories
# are:
# v1.05 is oldVersion
# v1.06 is currentVersion
# v1.07 is workingVersion
# before
# we would copy v1.07 to a new directory v1.08
# and then the directories would be linked as follows
# v1.06 is oldVersion
# v1.07 is currentVersion
# v1.08 is workingVersion
# oldVersion, currentVersion and workingVersion are symlinks to the
# actual version directories.
# slideshow and cpanel are also symlinks to the slideshow and cpanel
# directories within the version directories. For example v1.07 will
# have a slideshow and cpanel directory under it, so
# v1.07/slideshow/slideshow.php would be the slide show program's
# location.
echo;
echo;
#0. go to DOC_ROOT
cd
#1. get the real name of the workingVersion symlink which will be
# something like /homepages/45/d454707514/htdocs/v1.07
fullname=$(readlink -f workingVersion);
#2. extract the version part of the fullname which is like vN.NN (v1.07)
ver=$(basename $fullname);
echo Current workingVersion is $ver;
ver=${fullname##*/};
echo "ver=$ver";
#3. get the prefix part of the version: vN (like v1)
pre=${ver%%.*};
#4. get the suffix part of the version: NN (07)
subver=${ver##*.};
#5. Turn the NN which might be 0N into a decimal digit N. So 07 becomes
# 7 while 10 stays 10.
num=$(sed 's/^0*//'<<< $subver);
#6. Add on the the number
num=$((num +1));
#7. num could be less then 10, if so we have to pad the number with a
# leading zero so if num is 7 it becomes 07 and if it is 11 it stays 11
if [[ $num > 10 ]] ;
then
num="0"$num;
fi;
#8. extract the dirname part of the fullname
#dir=${fullname%%/v*};
#9. make the new working version
workingVersion=$pre.$num;
#10. copy the old working version's directory to the new working version
# directory.
echo cp -r $fullname $workingVersion;
#11. remove the old workingVersion symlink
echo rm workingVersion\; \# remove symlink;
#12. create the new symlink for the workingVersion
echo ln -s $workingVersion 'workingVersion'\; \# relink symlink;
echo --
#13. get the real name of the currentVersion symlink
fullnamecurr=$(readlink -f currentVersion);
currv=$(basename $fullnamecurr);
echo Current curentVersion is $currv;
#14. remove the oldVersion symlink
echo rm oldVersion\; \# remove symlink
#15. create the new symlink for oldVersion
echo ln -s $currv oldVersion\; \# relink symlink
echo --
#16. remove the symlink for currentVersion
echo rm currentVersion\; \# remove symlink
#17. create the new symlink for currentVersion
echo ln -s $currv currentVersion\; \# relink symlink

# now link up slideshow and cpanel
#18. Remove the two symlinks slideshow and cpanel
echo rm slideshow\; \# remove symlink
echo rm cpanel\; \# remove symlink
#19. create new symlinks for slideshow and cpanel
echo ln -s ${ver}/slideshow slideshow;
echo ln -s ${ver}/cpanel cpanel;
# All DONE.

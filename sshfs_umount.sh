
fn_stoponerror ()
{
	# Usage:
	# fn_stoponerror $? $LINENO
	er=$1
	lNo=$2
	if [ $er -ne 0 ]; then
		printf "\n$me: line $lNo: error [$er]\n"
		exit $er
	fi
}

dir0="$( cd "$( dirname "${BASH_SOURCE[0]}" )" >/dev/null 2>&1 && pwd )"
#sudo apt install sshfs
include="."

$include $dir0/settings.ignore.2.sh
fn_stoponerror $? $LINENO

$include $dir0/settings.sh
fn_stoponerror $? $LINENO

umount $settigsignore2_local_dir_mount
fn_stoponerror $? $LINENO

rm -f $settigsignore2_local_dir_mount
fn_stoponerror $? $LINENO

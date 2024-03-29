
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

mkdir -p "$settigsignore2_local_dir_mount"
fn_stoponerror $? $LINENO
#sshfs root@$settngs_machine_ip:/var/www $settigsignore2_dir_mount

sshfs -o password_stdin $settigsignore2_remote_server1@$settigsignore2_remote_server:$settigsignore2_remote_dir $settigsignore2_local_dir_mount <<< $(cat $dir0/settings.ignore.1)
fn_stoponerror $? $LINENO

dir0="$( cd "$( dirname "${BASH_SOURCE[0]}" )" >/dev/null 2>&1 && pwd )"
#sudo apt install sshfs
include="."
$include $dir0/settings.ignore.2.sh

$include $dir0/settings.sh

umount $settigsignore2_local_dir_mount

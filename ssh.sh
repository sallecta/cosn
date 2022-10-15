dir0="$( cd "$( dirname "${BASH_SOURCE[0]}" )" >/dev/null 2>&1 && pwd )"
#sudo apt install sshpass
include="."
$include $dir0/settings.ignore.2.sh

#ssh settigsignore2_remote_server1@$settigsignore2_remote_server
sshpass -f $dir0/settings.ignore.1 ssh $settigsignore2_remote_server1@$settigsignore2_remote_server

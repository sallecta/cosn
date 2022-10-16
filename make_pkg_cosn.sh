#!/usr/bin/env bash

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

include="."
$include $dir0/settings.sh
version=$(<version)
fn_stoponerror $? $LINENO

dir_src="$dir0/pkg_cosn"
dir_plg="plg_cosn"
dir_com="com_cosn"

dir_distr="$dir0/releases"
if [ ! -d "$dir_distr" ]; then
	mkdir $dir_distr
	fn_stoponerror $? $LINENO
fi

file_distr="pkg_""$settigs_app_name""_$version.zip"

if [ -f "$dir_distr/$file_distr" ]; then
	rm "$dir_distr/$file_distr"
fi

echo "package $settigs_app_name version $version"

cd "$dir_src/$dir_plg"
fn_stoponerror $? $LINENO

zip -rqFS  "$dir_distr/$dir_plg"".zip" "."
fn_stoponerror $? $LINENO

cd "$dir_distr"
fn_stoponerror $? $LINENO
zip -m  "$dir_distr/$file_distr" "$dir_plg"".zip"
fn_stoponerror $? $LINENO

cd "$dir_src/$dir_com"
fn_stoponerror $? $LINENO
zip -rqFS  "$dir_distr/$dir_com"".zip" "."
fn_stoponerror $? $LINENO

cd "$dir_distr"
fn_stoponerror $? $LINENO
zip -mq  "$dir_distr/$file_distr" "$dir_com"".zip"
fn_stoponerror $? $LINENO

cd "$dir_src"
fn_stoponerror $? $LINENO
zip -rq  "$dir_distr/$file_distr" "language"
fn_stoponerror $? $LINENO
zip -rq  "$dir_distr/$file_distr" "pkg_""$settigs_app_name"".xml"
fn_stoponerror $? $LINENO

cd "$dir0"
fn_stoponerror $? $LINENO

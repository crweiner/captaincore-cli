### Load configuration
#
#	Usage: Script/Run/snapshot.sh anchor.host
#
source ~/Scripts/config.sh

# Loop through arguments and seperate regular arguments from flags (--flag)
for var in "$@"
do
	# If starts with "--" then assign it to a flag array
    if [[ $var == --* ]]
    then
    	count=1+${#flags[*]}
    	flags[$count]=$var
    # Else assign to an arguments array
    else
    	count=1+${#arguments[*]}
    	arguments[$count]=$var
    fi
done

# Loop through flags and assign to varible. A flag "--email=austin@anchor.host" becomes $email
for i in "${!flags[@]}"
do

	# replace "-" with "_" and remove leading "--"
	flag_name=`echo ${flags[$i]} | tr - _`
	flag_name=`echo $flag_name | cut -c 3-`

	# detected flag contains data
	if [[ $flag_name == *"="* ]]; then
	  flag_value=`echo $flag_name | perl -n -e '/.+?=(.+)/&& print $1'` # extract value
	  flag_name=`echo $flag_name | perl -n -e '/(.+)?=.+/&& print $1'` # extract name
	  declare "$flag_name"="$flag_value" # assigns to $flag_flagname
	else
	  # assigns to $flag_flagname boolen
	  declare "flag_$flag_name"=true
	fi

done

if [ $# -gt 0 ]
then

	domain=${arguments[1]}

	## Generates snapshot archive
	timedate=$(date +%Y-%m-%d)
	tar -cvz --exclude=".git" --exclude="$site/wp-content/object-cache.php" --exclude="$site/wp-content/advanced-cache.php" --exclude=".gitignore" --exclude=".gitattributes" --exclude="_wpeprivate" -f $path_tmp/$domain-$timedate.tar.gz -C ~/Backup/ $domain/

	### Grab snapshot size in bytes
	if [[ "$OSTYPE" == "linux-gnu" ]]; then
	    ### Begin folder size in bytes without apparent-size flag
        snapshot_size=`du -s --block-size=1 $path_tmp/$domain-$timedate.tar.gz`
        snapshot_size=`echo $snapshot_size | cut -d' ' -f 1`

	elif [[ "$OSTYPE" == "darwin"* ]]; then
        ### Calculate folder size in bytes http://superuser.com/questions/22460/how-do-i-get-the-size-of-a-linux-or-mac-os-x-directory-from-the-command-line
        snapshot_size=`find $path_tmp/$domain-$timedate.tar.gz -type f -print0 | xargs -0 stat -f%z | awk '{b+=$domain} END {print b}'`
	fi

	## Moves snapshot to Backblaze archive folder
	$path_rclone/rclone move $path_tmp/$domain-$timedate.tar.gz Anchor-B2:AnchorHostBackup/Snapshots/$domain/

	# Post snapshot to ACF field
	curl "https://anchor.host/anchor-api/$domain/?storage=$snapshot_size&archive=$domain-$timedate.tar.gz&email=$email&token=$token"

fi

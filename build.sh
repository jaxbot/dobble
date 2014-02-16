echo "Building Dobble.me"
#rsync the dyan folders
rsync --verbose --progress --stats --recursive --times --perms --links --delete --exclude "adminer.php" ./dyan/ ./build/out/dyan/

php build/build.php $1
echo "Starting rsync..."
rsync --verbose  --progress --stats --compress --recursive --links --delete --exclude "*bak" --exclude "*~" --exclude "globals.php" ./build/out/ monitor@int.dobble:/var/www/dobble
echo "Deploying Dobble.me"

rsync --verbose  --progress --stats --compress --recursive --perms --chmod=o+rwx --links --delete --exclude "*bak" --exclude "*~" --exclude "globals.php" ./build/out/ monitor@dobble.me:/var/www/dobble
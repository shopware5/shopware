# verbose
set -v

STARTPARAMS=""

if [[ $DB_IMAGE == "mysql:8.0" ]]; then
    STARTPARAMS="mysqld --default-authentication-plugin=mysql_native_password"
fi

docker run -it --mount type=tmpfs,destination=/var/lib/mysql --name=mysqld -d -e MYSQL_ALLOW_EMPTY_PASSWORD=yes -e MYSQL_USER=travis -e MYSQL_PASSWORD=travis -e MYSQL_DATABASE=shopware -p3306:3306 ${DB_IMAGE} ${STARTPARAMS}
sleep 5

mysql() {
    docker exec mysqld mysql "${@}"
}
while :
do
    sleep 5
    mysql -e 'select version()'
    if [ $? = 0 ]; then
        break
    fi
    echo "server logs"
    docker logs --tail 5 mysqld
done

mysql -e 'select VERSION()'

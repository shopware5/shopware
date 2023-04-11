while ! mysqladmin ping -u shopware -pshopware; do
    echo Check mysql
	sleep 5
done

@servers(['production' => '-p 8022 root@162.213.254.96'])

@task('deploy', ['on' => 'production'])
cd /var/www/savemyga.me
git pull origin master
composer install
php artisan migrate --force
php artisan optimize
php artisan route:cache
php artisan config:cache
@endtask
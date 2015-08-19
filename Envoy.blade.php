@servers(['production' => '-p 8022 root@162.213.254.96'])

@task('deploy', ['on' => 'production'])
cd /var/www/savemyga.me
php artisan down
git pull origin master
composer install --no-dev
npm install
php artisan migrate --force
php artisan route:cache
php artisan config:cache
gulp --production
php artisan up
@endtask
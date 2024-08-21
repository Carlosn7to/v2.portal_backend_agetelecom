<?php

while (true) {
    exec('php /var/www/v2.portal/v2.portal_backend_agetelecom/artisan server:resources');
    sleep(1); // Aguarda 1 segundo
}

#! /bin/bash
php bin/console doc:sc:drop --force
php bin/console doc:sc:cr 
php bin/console doc:fix:lo --no-interaction
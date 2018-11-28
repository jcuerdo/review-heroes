<?php
namespace Deployer;

require '../app/vendor/deployer/deployer/recipe/common.php';

// Configuration

set('repository', 'git@github.com:YOURORGANIZATION/review-heroes.git');
//set('ssh_type', 'native');
//set('ssh_multiplexing', true);

// Servers

server('production-aws', 'YOUREC".compute.amazonaws.com')
    ->user('ec2-user')
    ->pemFile('../review-heroes.pem')
    ->set('deploy_path', '/home/ec2-user/review-heroes');

// Tasks

task('deploy:update', function () {
    run('sudo yum -y update');
});

task('deploy:install_git', function () {
    run('docker exec -i reviewheroes_phpfpm_1 apt-get -y update');
    run('docker exec -i reviewheroes_phpfpm_1 apt-get -y install git');
});

task('deploy:git_pull', function () {
    if ($revision = input()->getOption('revision')) {
        write("( Updating to revision $revision )");
        run("cd {{deploy_path}}/app && git pull origin $revision");
    } else {
        run('cd {{deploy_path}}/app && git pull origin master');
    }
});

task('deploy:crontab', function () {
    run("crontab < {{deploy_path}}/crontab/crontab");
});

task('deploy:docker_compose_up', function () {
    run('cd {{deploy_path}}/app && docker-compose -f ../docker-compose-prod.yml stop');
    run('cd {{deploy_path}}/app && docker-compose -f ../docker-compose-prod.yml build');
    run('cd {{deploy_path}}/app && docker-compose -f ../docker-compose-prod.yml up -d');
});

task('deploy:get_composer', function () {
    run('docker exec -i reviewheroes_phpfpm_1 php -r "copy(\'https://getcomposer.org/installer\', \'composer-setup.php\');"');
    run('docker exec -i reviewheroes_phpfpm_1 php composer-setup.php --quiet');
    run('docker exec -i reviewheroes_phpfpm_1 rm composer-setup.php');
});

task('deploy:composer_install', function () {
    run('docker exec -i reviewheroes_phpfpm_1 php composer.phar install');
});

task('deploy:redis_flush', function () {
    run('docker exec -i reviewheroes_phpfpm_1 redis-cli -h redis  -p 6379 FLUSHALL');
});

task('deploy:restart_containers', function () {
    run('cd {{deploy_path}} && docker-compose restart');
});

// Menu

desc('Deploy your project');
task('deploy', [
    'deploy:prepare',
    'deploy:lock',
    'deploy:update',
    'deploy:docker_compose_up',
    'deploy:install_git',
    'deploy:git_pull',
    'deploy:get_composer',
    'deploy:composer_install',
    'deploy:redis_flush',
    'deploy:restart_containers',
    'deploy:crontab',
    'deploy:unlock',
    'success'
]);

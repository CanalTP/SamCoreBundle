pipeline {
    agent any
    options {
        buildDiscarder(logRotator(numToKeepStr:'10'))
        disableConcurrentBuilds()
    }
    triggers{
        cron('H H(0-7) * * *')
    }
    parameters {
        string(name: 'sha1', defaultValue: 'master', description: '')
    }
    stages {
        stage('Build sam core image & create composer directory') {
            steps {
                sshagent (credentials: ['jenkins-kisio-bot']) {
                    sh '''
                    mkdir -p ${HOME}/.config/composer
                    _UID=$(id -u) GID=$(id -g) docker-compose -f docker-compose.test.yml build --no-cache --force-rm --pull samcore-app
                    '''
                }
            }
        }
        stage('Install dependencies') {
            steps {
                sshagent (credentials: ['jenkins-kisio-bot']) {
                    sh '''
                    rm -f composer.lock
                    _UID=$(id -u) GID=$(id -g) docker-compose -f docker-compose.test.yml run --rm samcore-app composer install --no-interaction --prefer-dist
                    '''
                }
            }
        }
        stage('Checkstyle') {
            steps {
                sshagent (credentials: ['jenkins-kisio-bot']) {
                    sh '''
                    _UID=$(id -u) GID=$(id -g) docker-compose -f docker-compose.test.yml run --rm samcore-app \
                    ./vendor/bin/phpcs -n --standard=PSR2 --encoding=utf-8 --extensions=php --ignore=vendor/* --ignore=Tests/* --report=checkstyle --report-file=checkstyle-result.xml . \
                    && true
                    '''
                }
            }
            post {
                always {
                    recordIssues enabledForFailure: true, tools: [checkStyle(pattern: 'checkstyle-result.xml')]
                }
            }
        }
        stage('Phpunit tests') {
            steps {
                sshagent (credentials: ['jenkins-kisio-bot']) {
                    sh '''
                    _UID=$(id -u) GID=$(id -g) docker-compose -f docker-compose.test.yml run --rm samcore-app \
                    ./vendor/bin/phpunit --testsuite=SamCoreUnit --log-junit=docs/unit/logs/junit.xml --coverage-html=docs/unit/CodeCoverage --coverage-clover=docs/unit/logs/coverage.xml
                    '''
                }
            }
            post {
                always {
                    step([
                          $class:'CloverPublisher',
                          cloverReportDir: 'docs/unit/CodeCoverage',
                          cloverReportFileName: 'coverage.xml',
                          healthyTarget: [methodCoverage: 70, conditionalCoverage: 70, statementCoverage: 70],
                          unhealthyTarget: [methodCoverage: 50, conditionalCoverage: 50, statementCoverage: 50],
                          failingTarget: [methodCoverage: 0, conditionalCoverage: 0, statementCoverage: 0]
                      ])
                    junit testResults: 'docs/unit/logs/junit.xml'
                }
            }
        }
    }
    post {
        always {
            echo 'Clean environment'
            sshagent (credentials: ['jenkins-kisio-bot']) {
                sh '''
                _UID=$(id -u) GID=$(id -g) docker-compose -f docker-compose.test.yml down --remove-orphans
                '''
            }
        }
    }
}

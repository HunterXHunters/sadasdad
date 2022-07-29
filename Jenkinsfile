pipeline {
    agent any

    stages {
        stage('GITLAB SCM') {
            steps {
               
                echo "Worked"
                
                
            }
        }
        
     
        stage('Snyk:Source Composition Analysis'){
            steps{
                echo "SCA"
                snykSecurity failOnIssues: false, snykInstallation: 'snyk', snykTokenId: 'snyk', targetFile: 'composer.lock'
                //snykSecurity failOnIssues: false, snykInstallation: 'snyk', snykTokenId: 'snyk', targetFile: 'requirements.txt'
                 //snykSecurity failOnIssues: false, organisation: 'python-devsecops', projectName: 'python-devsecops', severity: 'critical', snykInstallation: 'snyk', snykTokenId: 'snyk', targetFile: 'requirements.txt'
            }
        }
        
        stage('SonarQube: SAST & Code Quality'){
            steps{
                //def scannerHome = tool 'sonarqube';
                script {
                    scannerHome = tool 'sonarqube';
                    
                    
                }
                withSonarQubeEnv('sonarqube') {
                    sh "${scannerHome}/bin/sonar-scanner"
                }
                
            }
            
        }

        stage('Qualys Host Vulnerability Scan'){
            steps{
                echo "VA"
                //qualysVulnerabilityAnalyzer apiServer: 'https://qualysguard.qg1.apps.qualys.in/', credsId: 'TrailQualysEditionFreeTrail', hostIp: '129.213.152.156', network: 'ACCESS_FORBIDDEN', optionProfile: 'Auth Scans', platform: 'INDIA_PLATFORM', pollingInterval: '2', scanName: '[job_name]_jenkins_build_[build_number]', scannerName: 'External', useHost: true, vulnsTimeout: '60*2'
                //qualysVulnerabilityAnalyzer apiServer: 'https://qualysguard.qg1.apps.qualys.in/', credsId: 'TrailQualysEditionFreeTrail', hostIp: '129.213.152.156', network: 'ACCESS_FORBIDDEN', optionProfile: 'Auth Scans', platform: 'INDIA_PLATFORM', pollingInterval: '2', scanName: '[job_name]_jenkins_build_[build_number]', scannerName: 'test', useHost: true, vulnsTimeout: '60*2'
                //qualysVulnerabilityAnalyzer apiServer: 'https://qualysguard.qg1.apps.qualys.in/', credsId: 'TrailQualysEditionFreeTrail', hostIp: '0.0.0.0', network: 'ACCESS_FORBIDDEN', optionProfile: 'Auth Scans', platform: 'INDIA_PLATFORM', pollingInterval: '2', scanName: '[job_name]_jenkins_build_[build_number]', scannerName: 'test', useHost: true, vulnsTimeout: '60*2'
               //qualysVulnerabilityAnalyzer apiServer: 'https://qualysguard.qg1.apps.qualys.in/', credsId: 'Qualys Host Scan', hostIp: '150.136.14.67', network: '0', optionProfile: 'TMDSM-AuthScan', platform: 'INDIA_PLATFORM', pollingInterval: '2', scanName: '[job_name]_jenkins_build_[build_number]', scannerName: 'All_Scanners_in_Network', useHost: true, vulnsTimeout: '60*2'
            }
        }

        stage('DAST & Malware Detection'){
            steps{
                echo "DAST"
                qualysWASScan authRecord: 'none', cancelOptions: 'none', credsId: 'TrailQualysEditionFreeTrail', optionProfile: 'useDefault', platform: 'INDIA_PLATFORM', pollingInterval: '5', scanName: '[job_name]_jenkins_build_[build_number]', scanType: 'VULNERABILITY', vulnsTimeout: '60*24', webAppId: '19239792'
                //qualysWASScan authRecord: 'none', cancelOptions: 'none', credsId: 'FreetrailQualys', optionProfile: 'useDefault', platform: 'INDIA_PLATFORM', pollingInterval: '5', scanName: '[job_name]_jenkins_build_[build_number]', scanType: 'VULNERABILITY', vulnsTimeout: '60*24', webAppId: '15510008'
                //qualysWASScan authRecord: 'none', cancelOptions: 'none', credsId: 'FreetrailQualys', optionProfile: 'useDefault', platform: 'INDIA_PLATFORM', pollingInterval: '5', scanName: '[job_name]_jenkins_build_[build_number]', scanType: 'VULNERABILITY', vulnsTimeout: '60*48', webAppId: '14869663'
                //qualysWASScan authRecord: 'none', cancelOptions: 'none', credsId: 'FreetrailQualys', optionProfile: 'useDefault', platform: 'INDIA_PLATFORM', pollingInterval: '5', scanName: '[job_name]_jenkins_build_[build_number]', scanType: 'VULNERABILITY', vulnsTimeout: '60*28', webAppId: '14869661'
            }
        }
    }


 }

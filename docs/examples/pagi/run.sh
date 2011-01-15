#!/bin/bash
################################################################################
# Sample application to run from your dialplan.
################################################################################ 

# Location for your log4php.properties
export log4php_properties=/tmp/log4php.properties
export beans_xml=/tmp/beans.xml

# Make sure this is in the include path.
export PAGIBootstrap=example.php

# Your copy of PAGI, where src/ is.
pagi=/export/users/marcelog/src/sts/PAGI
ding=/export/users/marcelog/src/sts/Ding

# Your copy of log4php (optional)
log4php=/export/users/marcelog

# PHP to run and options
php=/usr/php-5.3/bin/php
phpoptions="-d include_path=${log4php}:${pagi}/src/mg:${ding}/src/mg:${ding}/docs/examples/pagi"

# Standard.. the idea is to have a common launcher.
launcher=${ding}/src/mg/Ding/Helpers/PAGI/PagiHelper.php

# Go!
${php} ${phpoptions} ${launcher}


#!/usr/bin/env bash

dir=$(cd $(dirname ${BASH_SOURCE[0]}) && pwd)

cd ${dir}/.. && docker build -t newtoncodes/ldap-account-manager .
cd ${dir}/.. && docker build -t newtoncodes/ldap-account-manager:5.2 .

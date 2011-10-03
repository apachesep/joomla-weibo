#!/bin/bash -x

version=0.6.2

rm -rf t_com_weibo
rm -rf t_plugin_weibo
mkdir t_com_weibo
mkdir t_com_weibo/admin
mkdir t_com_weibo/admin/language
mkdir t_com_weibo/admin/language/zh-CN
#mkdir t_com_weibo/language
#mkdir t_com_weibo/language/zh-CN
mkdir t_plugin_weibo
rm com_weibo.zip

./version.sh t_com_weibo $version com_weibo/*.php com_weibo/*.xml com_weibo/*.html
./version.sh t_com_weibo/admin $version com_weibo/admin/*.php com_weibo/*.html 
./version.sh t_com_weibo/admin/language/zh-CN $version com_weibo/admin/language/zh-CN/*.ini
#./version.sh t_com_weibo/language/zh-CN $version com_weibo/language/zh-CN/*.ini
#./version.sh t_com_weibo/language/en-GB $version com_weibo/language/en-GB/*.ini
./version.sh t_plugin_weibo $version plugin_weibo/*.php plugin_weibo/*.ini plugin_weibo/*.xml plugin_weibo/*.html

zip -r plugin_weibo.zip t_plugin_weibo -i \*.php \*.xml \*.ini \*.html
mv plugin_weibo.zip t_com_weibo/admin
zip -r com_weibo.zip t_com_weibo -i \*.php \*.xml \*.html 
zip -r com_weibo.zip t_com_weibo/admin -i \*.php \*.html \*.zip
zip -r com_weibo.zip t_com_weibo/admin/language/zh-CN -i \*.ini
#zip -r com_weibo.zip t_com_weibo/language/zh-CN  -i \*.ini
#zip -r com_weibo.zip t_com_weibo/language/en-GB  -i \*.ini



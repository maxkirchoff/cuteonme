# CuteOn.Me
A simple web application that uses the awe.sm APIs to get quick advice from your friends.  

Try it out at <http://CuteOn.Me>

Read more about [how it works](/awesm/cuteonme/docs/how-it-works.markdown)

## Setup

### Requirements
*  ubuntu

### Instructions

Install required packages

<code>
apt-get install php5 php5-curl apache2 git-core
</code>

Download the repo

<code>
git clone git@github.com:awesm/hackdisrupt.git cuteonme
</code>

Update the submodules

<code>
cd cuteonme
git submodule init
git submodule update
cd ..
</code>

Copy the application to apache's directory

<code> 
cp -R cuteonme /var/www/
</code>

Update the apache configuration

<code>
cp cuteonme/setup/cuteonme-apache-config /etc/apache2/sites-available/cuteonme
a2dissite default
a2ensite cuteonme
apache2ctl restart
</code>

Set www.cuteon.me as a valid hostname

<code>
vi /etc/hosts

\# add: 127.0.0.1    www.cuteon.me
</code>

Test your installation

<code>
curl http://www.cuteon.me/static/html/test-install.html

\# response should be: "Installation successful!"
</code>

Try out the app <http://CuteOn.Me>

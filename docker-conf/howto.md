# How to init Docker env. for MAMA-REST application

## Informations

there are two docker containers for the MAMA-REST application:
 - `mama-conf` based on `php:7.0-apache` container; it install all MAMA's php, modules, extensions, third-part dependencies...
 - `mama-rest` based on `mama-conf` container; it install all MAMA php scripts and php configurations files.

## build MAMA docker env. (optional)

note: if no modification has been made in the configuration, keep the old (and same) one! 
``` bash
# go into mama-rest directory
cd /path-to-mama-rest/
# go into 'docker-conf' sub directory
cd docker-conf
# build docker container
docker build -f mama-config-dockerfile -t npaulhe/mama-config:7.0 . 
```

## build MAMA docker REST app

note: you also can reuse an old image if you add a volum mount for `/var/www` directory with the MAMA-REST php sources.
``` bash
# go into mama-rest directory
cd /path-to-mama-rest/
# build MAMA-REST docker image
docker build -t npaulhe/mama-rest:7.0 .
 ```
 
## save images into files, copy them on docker-server, then deploy!

note: if `mama-config` docker container has not been updated, skip commandes for it! 
on your computer save docker container into image files
``` bash
# save docker container into image files on your computer
docker save -o $(pwd)/mama-config-7.0.image npaulhe/mama-config:7.0
docker save -o $(pwd)/mama-rest-7.0.image   npaulhe/mama-rest:7.0
# copy image files from your computer to the docker-server (into '/tmp/' directory)
scp /dir-to-save-img-on-your-computer/mama-* docker-server:/tmp/
```
... loggin into `docker-server` as root
``` bash
ssh docker server...
su -
```
... on `docker-server` deploy the new images
``` bash
# if an old image is already running, KILL IT!!!
docker ps
docker stop {sha1-of-mama-rest-running-container}
# deploy new new imagesimages
cat /tmp/mama_docker_images/mama-config.image  | sudo docker load
cat /tmp/mama_docker_images/mama-rest.image  | sudo docker load
# run new image
docker run \ 
	-d \ # deamon mode
	-v /mnt/test:/var/www/html/uploaded_files \ # mount uploaded file volume 
	-v /var/log/mama-mail:/var/www/logs \ # mount emails log directory
	-p 80:80 \ # port re-routing
	npaulhe/mama-rest # docker container name
```
... now enter into the container in order to set the configuration file! (set database connection, ...)
``` bash
# get new container id
docker ps
# enter into it
docker exec -it {sha1-of-mama-rest-running-container} bash
# into the container, add your favorit text editor (optional)
apt-get install nano vim vi
# into the container, edit the MAMA-REST application configuration file
vim conmfi/mama-config.ini
# enter `exit` to leave container bash
# back on `docker-server` promp? commit the changes into the container
docker commit -m "update databases files config" -a "YourFirstName YourLastName" {sha1-of-mama-rest-running-container} npaulhe/mama-rest
# optional: kill and re-launch the container
docker stop {sha1-of-mama-rest-running-container}
docker run -d -v /mnt/test:/var/www/html/uploaded_files -v /var/log/mama-mail:/var/www/logs -p 80:80 npaulhe/mama-rest
```

## Add MAMA cron

Note: instead of launching the cron script *via* the container sha1 (or short sha1) you can set a name for the conainter and use it!
``` bash
# edit the current crontab
crontab -e
# add the following rule
* * * * * /bin/docker exec -i {sha1-of-mama-rest-running-container}  php /var/www/html/jobby.php 1>> /dev/null 2>&1
```

## Run for local tests

```bash
docker run --rm -it -p 888:80 npaulhe/mama-rest:7.0
```

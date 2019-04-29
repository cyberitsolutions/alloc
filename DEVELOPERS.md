# Developing alloc

## Docker

Behold the 'simplicity' of Docker:

```
$ cd path/to/alloc/clone
$ docker build -t alloc .
$ docker run -p 4000:80 alloc
```

Then open http://localhost:4000/ and proceed with the installation.

You'll want to access mysql and write alloc_config.php, to do so (replace
`practical_boyd` with the name of your container):

```
$ docker exec -ti practical_boyd bash
```

## LXC

These instructions assume you are using Debian, but *should* work on
Ubuntu. Read the LXC wiki page for your distro.

### Install and Setup LXC

You will need to install `lxc` and `bridge-utils`:

```
# apt-get install lxc bridge-utils
```

Since Debian does not create a default network, you will have to set
it up yourself. We are going to do that with `veth` (see `man veth`),
but if you have some fancy pants computer with cool hardware for
networks, by all means do cool stuff! Sadly the LXC manpages leave a
lot to be desired, I recommend reading your distro's wiki on the
matter.

In `/etc/lxc/default.conf` replace `lxc.network.type = empty` with:

```
lxc.network.type = veth
lxc.network.link = lxcbr0
lxc.network.flags = up
lxc.network.hwaddr = 00:16:3e:xx:xx:xx
```

for LXC 3.x (Debian Buster):

```
lxc.net.0.type = veth
lxc.net.0.link = lxcbr0
lxc.net.0.flags = up
lxc.net.0.hwaddr = 00:16:3e:xx:xx:xx
```

This will create a new interface called `lxcbr0` with a mac-address
starting with `00:16:3e:`. Now we need to configure the LXC bridge in
`/etc/default/lxc-net` with these lines:

```
USE_LXC_BRIDGE="true"
LXC_BRIDGE="lxcbr0"
LXC_ADDR="10.0.3.1"
LXC_NETMASK="255.255.255.0"
LXC_NETWORK="10.0.3.0/24"
LXC_DHCP_RANGE="10.0.3.2,10.0.3.254"
LXC_DHCP_MAX="253"
LXC_DHCP_CONFILE=""
LXC_DOMAIN=""
```

This will allow us to access the container at a `10.0.3.X` address,
e.g. <http://10.0.3.51/>.

You should restart the `lxc-net` service:

```
$ systemctl restart lxc-net.service
```

Now create a container for you alloc development work!

```
# lxc-create --name alloc-dev -t download
```

This will give us a list of possible distros, and then a few prompts
asking what we want. E.g. type `debian RET stretch RET amd64 RET`
(`RET` is return or enter) to get Debian Stretch 64-bit.


A few helpful commands are listed here. To view our containers:

```
# lxc-ls -f
```

To start or stop a container:

```
# lxc-start --name alloc-dev
# lxc-stop --name alloc-dev
```

To attach to a container:

```
# lxc-attach --name alloc-dev
```

### Installing alloc in your LXC container

First attach to your container with `lxc-attach`, e.g.:

```
# lxc-attach --name alloc-dev
```

Next we need to install apache, php , php-mysql, php-mbstring php-gd,
mariadb (mysql), make, python, and git:

```
# apt-get install apache2 php php-mysql php-mbstring php-gd mariadb-server make python git
```

Ensure mariadb is running in utf8 mode and not utf8mb4, see the config
files for mariadb in /etc/.

Now restart apache and mariadb:

```
# systemctl restart apache2; systemctl restart mysql
```

We need to create and change the ownership of `/var/local/alloc/`,
this saves use getting part way through the alloc installer and
getting and error saying we need to do this.

```
# mkdir -p /var/local/alloc/; chown www-data /var/local/alloc/
```

You should also remove the default Debian apache2 page:

```
# cd /var/www/html/; rm index.html
```

Now clone your "fork" of alloc, replacing `<username>` with your
GitHub username:

```
# git clone https://github.com/<username>/alloc /var/www/html/
```

Next make the patches and CSS:

```
# make patches; make css
```

Then finally get the IP address of your container from you local
machine with:

```
# lxc-ls -f
```

and browse to that IP in your browser to follow the rest of the alloc
install instructions.

### The development process

This setup allows you to make changes to you local machines copy of
alloc, push them to your GitHub "fork", then pull them into your LXC
container at which point you should restart apache and mysql again:

```
# systemctl restart apache2; systemctl restart mysql
```

Now party! 🎉😃

Be sure to `git rebase` your changes once done, and 'squish' changes
into a single commit, then force push to your "fork". If you have
write access to the main alloc repo, **NEVER** rebase and force push
to the main alloc repo! This will mess up other peoples clones.

## Alloc coding standards

We follow the [PSR-2](https://www.php-fig.org/psr/psr-2/) coding
standard with the exception of `else if`. Please use `else if` instead
of `elseif`.

Because the alloc code base did not use PSR-2 until late 2018, many
class/method names do not match the PSR-2 recommendations, this is an
ongoing effort.

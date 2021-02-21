# System
```sh
$ apt install anacron logwatch chrony postfix
```

# Tools
```sh
$ apt install tree aptitude mailutils htop glances dnstracer tree ipcalc jq iperf3
```

# Security
```sh
$ apt install fail2ban portsentry nmap netcat
```

# Web & Mail
```
$ add-apt-repository ppa:ondrej/nginx
$ apt update
$ apt install apache2-utils nginx-full libnginx-mod-http-fancyindex fcgiwrap mailgraph
$ mkdir -p /var/lib/mailgraph/,mailgraph
$ chown www-data:www-data /var/lib/mailgraph/,mailgraph
```

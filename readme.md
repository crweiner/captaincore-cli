<h1 align="center">
  <a href="https://captaincore.io"><img src="https://captaincore.io/wp-content/uploads/2018/02/main-web-icons-captain.png" width="70" /></a><br />
CaptainCore CLI

</h1>

[CaptainCore](https://captaincore.io) is a powerful toolkit for managing WordPress sites via SSH & WP-CLI. Built using [Bash CLI](https://github.com/SierraSoftworks/bash-cli). Integrates with the [CaptainCore GUI (WordPress plugin)](https://github.com/captaincore/captaincore-gui).

[![emoji-log](https://cdn.rawgit.com/ahmadawais/stuff/ca97874/emoji-log/flat.svg)](https://github.com/ahmadawais/Emoji-Log/)

## **Warning**
This project is under active development and **not yet stable**. Things may break without notice. Only proceed if your wanting to spend time on the project. Sign up to receive project update at [captaincore.io](https://captaincore.io/).

## Getting started

Recommend spinning up a fresh VPS running Ubuntu 18.04 with:
- [Digital Ocean](https://www.digitalocean.com/) - great for most people.
- [Backupsy](https://backupsy.com/) - great for cheap storage.

Eventually all of these steps will be wrapped into a single kickstart.sh script. Until then here are the barebones steps to begin.

- Download `git clone https://github.com/captaincore/captaincore-cli.git ~/.captaincore-cli/`
- Install `captaincore` command globally by running `sudo ln -s ~/.captaincore-cli/cli /usr/local/bin/captaincore`
- Download [latest rclone](https://rclone.org/downloads/) and install system wide by running `sudo ln -s ~/Download/rclone-v1.46-linux-amd64/rclone /usr/local/bin/rclone`
- Run `rclone config` and add your [cloud storage providers](https://rclone.org/overview/). Recommend Backblaze B2 for backups/snapshots and Dropbox for log files as they require link sharing support.
- Install PHP: `sudo apt-get install -y php7.0 php7.0-fpm php7.0-cli php7.0-common php7.0-mbstring php7.0-gd php7.0-intl php7.0-xml php7.0-mysql php7.0-mcrypt php7.0-zip`
- Install WP-CLI: [Refer to Offical Docs](https://make.wordpress.org/cli/handbook/installing/)
- Install JSON package: `sudo npm install --global json`
- Install MariaDB: `sudo apt-get install mariadb-server mariadb-client && sudo systemctl enable mysql.service && sudo systemctl start mysql.service && sudo mysql_secure_installation`
- Copy MariaDB root password to `~/.captaincore-cli/config` as `local_wp_db_pw="<db-password>"`
- Copy `config.sample` to `config` and configure `Local Paths`, `Remote Paths` and `Vars`
- Run `captaincore cli install`

Long running tasks like `captaincore copy` or `captaincore move` can prematurely end due to SSH idle. In order to help reduce disconnects add the following to  `~/.ssh/config` which will keep SSH alive by sending a signal every four minutes (240 seconds) to the remote host.

```
Host *
ServerAliveInterval 240
```

## Usage

### How site names work

CaptainCore uses arbitrary site names. When managing multiple sites, there needs to be a way to uniquely identify each site. While domain names seems like a good option, there are times you may want to be managing the same site with the same domain on multiple host providers. Also domain names can be long and sometimes change. On of flip side using a completely arbitrary site ID isn't very human friendly. A site name is something in between. A short but meaningful name that is unchangeable even if the domain name changes.

Site names can also specify a provider using an @ symbol `<site>@<provider>`. This makes dealing with multiple host providers enjoyable. Here's an example coping a site between providers `captaincore copy anchorhost@wpengine anchorhost@kinsta`. Omitting the provider is completely valid however won't be very particular if multiple site names exist.

### Targeting sites

Many commands also support targeting many sites. To target use `@all`, `@production` or `@staging` after the command. These can be combined to filter sites further by chaining other modifiers after the target. For example `@production.updates-on` will target production sites are marked for automatic updates and `@all.offload-on` will target all sites which have offload enabled. 

This allows for flexible repeat process. For example updating themes/plugins on production sites every week with `captaincore update @production.updates-on` and then monthly on staging sites with `captaincore update @staging.updates-on`.

### Fleet mode

With fleet mode enabled a single CaptainCore instance can support sites for many different GUIs (or known as captains). Each captain only has ability to run commands on their respective sites. Internally this works by passing `--captain_id=<captain_id>` onto each `captaincore <command>`. Commands run without a `--captain_id` will default to ID 1. 

Any command can be run across the entire fleet using `--fleet`. For example running `captaincore backup @production --fleet` will loop through all CaptainIDs. For a fleet with 3 CaptainIDs that command will run `captaincore backup @production --captain_id=1`, `captaincore backup @production --captain_id=2` and `captaincore backup @production --captain_id=3`.

## Commands

Shows help

`captaincore help`

Adds a site to CaptainCore CLI.

```
captaincore site add <site> --id=<id> --domain=<domain> --username=<username> --password=<password> --address=<address> --protocol=<protocol> --port=<port> --staging_username=<staging_username> --staging_password=<staging_password> --staging_address=<staging_address> --staging_protocol=<staging_protocol> --staging_port=<staging_port> [--preloadusers=<preloadusers>] [--homedir=<homedir>] [--s3accesskey=<s3accesskey>] [--s3secretkey=<s3secretkey>] [--s3bucket=<s3bucket>] [--s3path=<s3path>]
```

Updates a site in CaptainCore CLI.

```
captaincore site update <site> --id=<id> --domain=<domain> --username=<username> --password=<password> --address=<address> --protocol=<protocol> --port=<port> --staging_username=<staging_username> --staging_password=<staging_password> --staging_address=<staging_address> --staging_protocol=<staging_protocol> --staging_port=<staging_port> [--preloadusers=<preloadusers>] [--homedir=<homedir>] [--s3accesskey=<s3accesskey>] [--s3secretkey=<s3secretkey>] [--s3bucket=<s3bucket>] [--s3path=<s3path>]
```

Removes a site from CaptainCore CLI.

```
captaincore site delete <site>
```

Backups one or more sites.

```
captaincore backup [<site>...] [@<target>] [--use-direct] [--skip-remote] [--skip-db] [--with-staging]
```

Get details about a site.

```
captaincore site get <site> [--field=<field>] [--bash]
```

Creates [Quicksave (plugins/themes)](https://anchor.host/introducing-quicksaves-with-rollbacks/) of website

```
captaincore quicksave [<site>...] [@<target>] [--force] [--debug]
```

Rollback from a Quicksave (theme/plugin)

```
captaincore rollback <site> <commit> [--plugin=<plugin>] [--theme=<theme>] [--all]
```

Login to WordPress using links

```
captaincore login <site> <login> [--open]
```

SSH wrapper

```
captaincore ssh [<site>..] [@<target>] [--command=<commands>] [--script=<name|file>] [--<script-argument-name>=<script-argument-value>]
```

Snapshots one or more sites.

```
captaincore snapshot [<site>...] [@<target>] [--email=<email>] [--skip-remote] [--delete-after-snapshot]
```

Shows last 12 months of stats from WordPress.com API or self hosted Fathom.

```
captaincore stats <site>
```

Updates themes/plugins on WordPress sites

```
captaincore update [<site>...] [@<target>] [--exclude-themes=<themes>] [--exclude-plugins=<plugins>] [--<field>=<value>]
```

List sites

```
captaincore site list [@<target>] [--filter=<theme|plugin|core>] [--filter-name=<name>] [--filter-version=<version>] [--filter-status=<active|inactive|dropin|must-use>] [--field=<field>]
```

## Real World Examples

Downgrade WooCommerce on sites running a specific WooCommerce version

```
captaincore ssh $(captaincore site list --filter=plugin --filter-name=woocommerce --filter-version=3.3.0) --command="wp plugin install woocommerce --version=3.2.6"
```

Upgrade Ultimate Member plugin on sites with it installed

```
for site in $(captaincore site list --filter=plugin --filter-name=ultimate-member); do
  captaincore ssh $site --command="wp plugin update ultimate-member"
done
```

Fix bug with Mailgun plugin by patching in missing region setting.

```
for site in $(captaincore site list --filter=plugin --filter-name=mailgun); do
  captaincore ssh $site --command="wp option patch insert mailgun region us"
done
```

Backup sites

```
captaincore backup @all
captaincore backup @production
captaincore backup @staging
```

Generate quicksave on all sites

```
captaincore quicksave @all
```

Monitor check all sites

```
captaincore monitor @all
```

Run WordPress theme/plugin updates on production sites which have been marked for automatic updates

```
captaincore update @production.updates-on
```

Launch site. Will change default Kinsta/WP Engine urls to real domain name and drop search engine privacy.

```
captaincore ssh <site-name> --script=launch --domain=<domain>
```

Update WordPress core on all sites

```
captaincore ssh @all --command="wp core update; wp core update-db"
```

Find and replace http to https urls

```
captaincore ssh <site-name> --script=apply-https
```

## License
This is free software under the terms of MIT the license (check the LICENSE file included in this package).

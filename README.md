Coefficient Magento extension
=============================

Local development
----------------

You need a local install of Magento somewhere, for example `/opt/sites/magento`.
Download the free Community Edition [here](http://www.magentocommerce.com/download).

### Modman

Without Modman you would have to manually copy (or link) the various directories and
files in our extension to various directories (and files) in your local Magento
install. Modman manages copying or linking these directories and files for us. This
lets us keep our Magento install and our extension clone separate.

Modman is available at https://github.com/colinmollenhour/modman. You can use its
installer or just download https://raw.github.com/colinmollenhour/modman/master/modman
directly.  Move it somewhere like `/usr/local/bin` and make it executable.

#### Modman usage

*Note: For some reason I couldn't get Modman to work unless I was root. I double
checked my permissions, and the directories of my Magento install are writable by
my user (vagrant) but for whatever reason, I could only get Modman to work as root.
You may be able to get it to work as your user, but I spent a while troubleshooting
this and was never able to figure it out. So if you run `modman` as your regular
user and it doesn't work, save ourself some time and headache and just run it as root.*

`cd` to the root of your local Magento install. If you haven't before, run `modman init`
which will create a `.modman` directory.

Next, from within the root of your local Magento install (e.g. `/opt/sites/magento`),
run `modman link /path/to/coefficient-magento` which will symlink all of our
extension's files and directories to their appropriate places in our local Magento
install.

### Magento config
You need to enable "Allow Symlinks" in Magento's config. This is outlined in Modman's
docs at https://github.com/colinmollenhour/modman#requirements.

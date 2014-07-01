Coefficient extension for Magento
=================================

Coefficient helps you understand your customers and grow your business. We are 
automated customer research for direct-to-consumer brands.

This extension exposes a simple REST API that Coefficient uses to download your
store's customers, products, and orders.

Coefficient is currently in private beta. This extension supports Magento
versions 1.8.1 and newer. It may work in older versions of Magento but this has
not been tested. It was developed in PHP 5.3 and may work with older versions of
PHP but this too has not been tested.

Find out more about Coefficient and request a demo at [coefficientapp.com](https://coefficientapp.com).

Installation
------------

[This](http://gotgroove.com/ecommerce-blog/magento-development/developer-toolbox-a-guide-for-installing-magento-extensions/)
is a good installation guide written by someone else.

Once installed, this extension exposes an API key in **System -> Configuration ->
Coefficient -> Coefficient Extension**. You'll need to copy this key into the API
integrations section in Coefficient. Note that your store must support HTTPS.
This extension will not work over HTTP.

Local Development
-----------------

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
my user but for whatever reason, I could only get Modman to work with elevated privileges.
You may be able to get it to work as your user but I spent a while troubleshooting
this and was never able to figure it out. So if you run `modman` as your regular
user and it doesn't work, save yourself some time and headache and just run it as root.*

`cd` to the root of your local Magento install. If you haven't before, run `modman init`
which will create a `.modman` directory.

Next, from within the root of your local Magento install (e.g. `/opt/sites/magento`),
run `modman link /path/to/coefficient-magento` which will symlink all of our
extension's files and directories to their appropriate places in our local Magento
install.

### Magento config
You need to enable "Allow Symlinks" in Magento's config. This is outlined in Modman's
docs at https://github.com/colinmollenhour/modman#requirements.

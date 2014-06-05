# N3O CMS is free PHP content management system & web page framework.

web: [blaz.at/home](http://blaz.at/home)
twitter: [@blazoncek](http://twitter.com/#!/blazoncek)

Originally developed in ColdFusion CFML and later ported to PHP, N3O CMS is extensible multilingual content management system providing scalable, easy to use CMS.
N3O CMS runs on mobile and desktop and has been tested on Chrome, Firefox, Safari and Internet Explorer 8 and above.

## Latest Release v2.2.1

- Initial GitHub commit.

**Important notes about required libraries**

N3O CMS depends on the following opensource libraries/modules:

- TinyMCE for rich text editing [tinymce.com](http://www.tinymce.com)
- ezSQL for simplified and portable SQL server access [jv2222/ezSQL](https://github.com/jv2222/ezSQL)
- jQuery (UI & mobile) for client side processing [jquery.com](http://jquery.com)
- PHPMailer for SMTP/IMAP/POP3 server access [PHPMailer](https://github.com/PHPMailer/PHPMailer)
- PHPThumb for thumbnail generation and image resizing [phpthumb.gxdlabs.com](http://phpthumb.gxdlabs.com)
- mobile_device_detect for detecting mobile devices [detectmobilebrowsers.mobi](http://detectmobilebrowsers.mobi/)
- Photoswipe for image display on mobile devices [photoswipe.com](http://www.photoswipe.com)
- Fancybox for image display on regular clients [fancybox.net](http://fancybox.net)
- some other jQuery plugins included in the repo for convenience (datebox,fileupload,form,ui.widget,retina.js)

## Features

- Multilingual content management system.
- Multiuser access rights and content protection.
- Works on most modern browsers.
- Mobile browser optimized.
- Extendable with custom modules.
- Separate content generation and database access.
- Integrated forum/discussions.
- Integrated image galleries (independent or attached to texts).
- List and grid text views. Including different text types.
- Automatic image resizing and thumbnail generation.
- Allows file attachments to texts and content categories.
- Text cross linking.
- Drag & drop support for image uploads.
- Flexible CSS generation (uses PHP to generate CSS).
- RSS and mailing lists support (newsletter distribution).
- Social media integration including:
    - Google Maps integration (including uploaded GPX support).
    - Twitter links and authors.
    - Facebook likes.
    - Google+ likes.
- Multiple database support (currently only MySQL syntax implemented).

## Getting Started

Create a (MySQL) database and appropriate user. Assign permissions for user (all permissions).

Create _config.php from _config.sample.php and modify SQL connection parameters (use database name and user from previous step). Adjust other parameters as needed.

Visit the web page http://www.yourdomain.com/servis/db-init.php which will create tables and populate the database. You can delete or rename servis/db-init.php after DB has been created for security reasons.

Login to the maintenance page at http://www.yourdomain.com/servis/. Use "admin" for username and "Pa$$w0rD" for initial password. You should change default password ASAP.

### Getting Started - Default Distribution

Database is populated with some default values to get you started. You can (and should) change most of the values.

## License

N3O CMS is released under [LGPLv3](http://www.gnu.org/licenses/lgpl-3.0.html). Please read COPYING.LSSER and COPYING for information on the software availability and distribution.

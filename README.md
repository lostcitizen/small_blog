small_blog
==========

Minimalistic PHP blogging engine, using a single php class (smallblog). Useful to create a blog using existing website template. Requires administrator capabilities.


![small_blog][image]
[image]: https://raw.github.com/pontikis/small_blog/dev/small_blog.png

Requires:
--------
* php 5+
* mod_rewrite for URL rewrite
* a common database (MySQL, PostgreSQL have tested at this time)
* php ADODB (to connect with various databases)

Conventions:
--------
* Post html is stored to file having same name with post url (the part after host name)
* Dates are stored in database as UTC timestamp YYYYMMDDHHMMSS (in varchar(14) field)

Features:
--------
* Nice URLs
* Only one table (posts) is used (post_title, post_sub_title, tags, date_published, impressions)
* $smallblog->getPosts($start, $posts): returns selected range posts data
* $smallblog->getPostByURL($url): returns post data from URL
* $smallblog->increasePostImpressions($post_id): increase post impressions
* $smallblog->getPostsByTAG($tag): returns posts URLs and dates to create a tag page
* $smallblog->getBlogArchive(): returns posts URLs and dates to create an archive page


Download
-------
Download [here][DOWNLOAD]
[DOWNLOAD]: https://github.com/pontikis/small_blog/archive/master.zip
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
* $smallblog->getPosts($offset, $posts_per_page, $date_start, $date_end): returns selected range posts meta-data
* $smallblog->getPostByURL($url): returns post meta-data from URL
* $smallblog->increasePostImpressions($post_id): increase post impressions
* $smallblog->getPostsByTAG($tag, $date_start, $date_end): returns posts meta-data to create a tag page
* $smallblog->getBlogArchive($date_start, $date_end): returns posts meta-data to create an archive page


Download
-------
Download [here][DOWNLOAD]
[DOWNLOAD]: https://github.com/pontikis/small_blog/archive/master.zip
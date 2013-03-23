small_blog
==========

Minimalistic PHP blogging engine, using a single php class (smallblog). Useful to include a blog inside existing website. Requires administrator capabilities.

![small_blog][image]
[image]: https://raw.github.com/pontikis/small_blog/master/small_blog.png

Project page: [https://pontikis.net/labs/small_blog][HOME]
[HOME]: http://pontikis.net/labs/small_blog

Copyright Christos Pontikis [http://pontikis.net][copyright]
[copyright]: http://pontikis.net

License [MIT][mit]
[mit]: https://raw.github.com/pontikis/small_blog/master/MIT_LICENSE


Requires:
--------
* php 5+
* mod_rewrite for URL rewrite
* a common database (MySQL, PostgreSQL have tested at this time)
* php ADODB (to connect with various databases)

Conventions:
--------
* Only one table (posts) is used to keep posts meta-data (post_title, post_sub_title, tags, category_id date_published, impressions)
* Post html stored in files on disk
* Tags stored in database as: |tag1|tag2|tag3| (delimeter may be another character than |)
* Dates are stored in database as UTC timestamp YYYYMMDDHHMMSS (in varchar(14) field)

![small_blog_db][db]
[db]: https://raw.github.com/pontikis/small_blog/master/small_blog_db.png

Features:
--------
* Nice URLs
* $smallblog->getPosts($offset, $posts_per_page, $tag, $tag_delim, $ctg_id, $date_start, $date_end, $count, $popular): returns selected range posts meta-data (or posts count if count = true, or most popular posts meta-data if popular = true)
* $smallblog->getPostByURL($url): returns post meta-data from URL
* $smallblog->increasePostImpressions($post_id): increase post impressions
* $smallblog->getNearPost($date, 'next'): get next (or previous) post

Download
-------
Current release 0.8.0 Download [here][DOWNLOAD]
[DOWNLOAD]: https://github.com/pontikis/small_blog/archive/master.zip


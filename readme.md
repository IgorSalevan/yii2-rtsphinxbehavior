# Behavior for working with Sphinx RealTime indexes

### How to attach

```
namespace modules\blog\models;
use modules\blog\Module;
use modules\blog\components\RtSphinxBehavior;

class Post extends \yii\db\ActiveRecord {

    public function behaviors() {
        return [
                    'rtSphinxBehavior' => [
                        'class' => RtSphinxBehavior::className(),
                        'rtIndex' => Yii::$app->getModule('blog')->getParam('sphinxRtIndex'),
                        'idAttributeName' => 'id',
                        'rtFieldNames' => ['name', 'title', 'description', 'text'],
                        'rtAttributeNames' => ['category_id'],
                        'enabled' => Yii::$app->getModule('blog')->getParam('isSphinxEnabled'),
                    ],
                ];   
    }
```

In this very example the `rtIndex` parameter gets value from blog-module paramener `sphinxRtIndex`.
Then provide the names of attributes and fields from our main document fetch query, that is described in sphinx.conf in the source block.

### How to configure RT index for Sphinx

```
source is_src
{
	type			= mysql

	sql_host		= localhost
	sql_user		= root
	sql_pass		=
	sql_db			= cms_db
	sql_port		= 3306	# optional, default is 3306

	sql_query_pre = SET NAMES utf8
    sql_query_pre = SET CHARACTER SET utf8
	
	sql_query = \
		SELECT id, category_id, UNIX_TIMESTAMP(date) AS date, name, title, description, text \
		FROM post

	sql_attr_uint		= category_id
	
	sql_field_string    = name
	sql_field_string    = title
	sql_field_string    = description	
	sql_field_string    = text	
}
```

The rt block looks like this:

```
index is_rt
{
	type			= rt
	docinfo			= extern
	
	rt_mem_limit	= 512M

	path			= /sphinx/data/is_rt
	stopwords		= /sphinx/stop/words.txt
	dict			= keywords
	morphology		= stem_ru, stem_en, soundex
	min_word_len	= 3
	min_prefix_len 	= 3
	expand_keywords	= 1
	index_exact_words = 1
	html_strip 		= 1
	
	rt_field = name 	
	rt_field = title
	rt_field = description
	rt_field = text
	
	rt_attr_uint = category_id
}
```

### How it works

`RtSphinxBehavior` triggers on insert | update | delete events, processed by `ActiveRecord` class.

### Resume

In mentioned above example I placed `RtSphinxBehavior.php` in the directory `modules/blog/components`, so this structure 
describes the situation when we use behavior only for one module, named `blog`.  

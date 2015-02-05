{root:}<?xml version="1.0" encoding="UTF-8" ?> 
<rss version="2.0">
	<channel>
	<title>{title}</title> 
	<link>{link}</link> 
	<description>{description}</description> 
	<lastBuildDate>{~date(:r,time)}</lastBuildDate>

	
	{items::item}
	
	</channel>
</rss>
	{item:}
	<item>
		<title>{title}</title>
		<link>{link}</link>
		<description>{description}</description>
		<pubDate>{~date(:r,pubDate)}</pubDate>
	</item>
	
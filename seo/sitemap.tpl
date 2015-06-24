{robots:}User-agent: *
sitemap: http://{host}/{root}?*seo/seo.php?type=sitemap
Disallow: /infra/cache/
Disallow: /infra/backup/
Disallow: /infra/lib/
{sitemap:}<?xml version="1.0" encoding="UTF-8"?>
<urlset xmlns="http://www.sitemaps.org/schemas/sitemap/0.9">{list::item}</urlset>
{item:}
	<url>
		<loc>
			http://{host}/{root}{q}{~encode(link)}
		</loc>
	</url>
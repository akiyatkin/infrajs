{robots:}User-agent: *
sitemap: http://{host}/{root}infra/plugins/seo/seo.php?type=sitemap
Disallow: /infra
Allow: /infra/data
Allow: /infra/plugins/imager
Allow: /infra/plugins/infra
Allow: /infra/plugins/files
Allow: /infra/plugins/pages
Allow: /infra/plugins/seo
Allow: /infra/plugins/autoedit/icons
{sitemap:}<?xml version="1.0" encoding="UTF-8"?>
<urlset xmlns="http://www.sitemaps.org/schemas/sitemap/0.9">{list::item}</urlset>
{item:}
	<url>
		<loc>
			http://{host}/{root}{q}{~encode(link)}
		</loc>
	</url>
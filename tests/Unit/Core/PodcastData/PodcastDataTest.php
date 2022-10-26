<?php
declare(strict_types=1);

namespace OCA\GPodderSync\Tests\Unit\Core\PodcastData;

use OCA\GPodderSync\Core\PodcastData\PodcastData;
use Test\TestCase;

class EpisodeActionTest extends TestCase {
	public function testToArray(): void {
		$podcastData = new PodcastData('title1', 'author1', 'http://example.com/', 'description1', 'http://example.com/image.jpg', 1337);
		$expected = [
			'title' => 'title1',
			'author' => 'author1',
			'link' => 'http://example.com/',
			'description' => 'description1',
			'imageUrl' => 'http://example.com/image.jpg',
			'imageBlob' => null,
			'fetchedAtUnix' => 1337,
		];
		$this->assertSame($expected, $podcastData->toArray());
	}

	public function testFromArray(): void {
		$podcastData = new PodcastData('title1', 'author1', 'http://example.com/', 'description1', 'http://example.com/image.jpg', 1337);
		$expected = $podcastData->toArray();
		$fromArray = PodcastData::fromArray($expected);
		$this->assertSame($expected, $fromArray->toArray());
	}

	public function testParseRssXml(): void {
		$xml = '<?xml version="1.0" encoding="UTF-8"?>
		<rss version="2.0"
		  xmlns:atom="http://www.w3.org/2005/Atom"
		  xmlns:content="http://purl.org/rss/1.0/modules/content/"
		  xmlns:dc="http://purl.org/dc/elements/1.1/"
		  xmlns:itunes="http://www.itunes.com/dtds/podcast-1.0.dtd"
		  xmlns:podcast="https://podcastindex.org/namespace/1.0"
		>
			<channel>
				<title>The title of this Podcast</title>
				<copyright>All rights reserved</copyright>
				<link>http://example.com</link>
				<atom:link href="https://example.com/feed" rel="self" type="application/rss+xml" />
				<atom:link href="https://example.com" rel="alternate" type="text/html" />
				<language>en-us</language>
				<description>Some long description</description>
				<itunes:author>The Podcast Author</itunes:author>
				<itunes:summary>Some long description</itunes:summary>
				<itunes:explicit>no</itunes:explicit>
				<itunes:image href="https://example.com/image.jpg"/>
				<itunes:keywords>nextcloud, gpodder</itunes:keywords>
				<itunes:owner>
					<itunes:name>Owner of the podcast</itunes:name>
					<itunes:email>editors@example.com</itunes:email>
				</itunes:owner>
				<itunes:category text="Technology">
					<itunes:category text="Podcast Tools"/>
				</itunes:category>
				<podcast:funding url="https://example.com/funding">Support our work</podcast:funding>
				<podcast:person role="host" img="https://avatars.githubusercontent.com/u/15801468?s=80&amp;v=4" href="https://github.com/thrillfall">thrillfall</podcast:person>
				<podcast:person role="host" img="https://avatars.githubusercontent.com/u/2477952?s=80&amp;v=4" href="https://github.com/jilleJr">jilleJr</podcast:person>
			</channel>
		</rss>
		';

		$podcastData = PodcastData::parseRssXml($xml, 1337);
		$expected = [
			'title' => 'The title of this Podcast',
			'author' => 'The Podcast Author',
			'link' => 'http://example.com',
			'description' => 'Some long description',
			'imageUrl' => 'https://example.com/image.jpg',
			'imageBlob' => null,
			'fetchedAtUnix' => 1337,
		];
		$this->assertSame($expected, $podcastData->toArray());
	}

	public function testParseRssXmlPartial(): void {
		$xml = '<?xml version="1.0" encoding="UTF-8"?>
		<rss version="2.0"
		  xmlns:atom="http://www.w3.org/2005/Atom"
		  xmlns:content="http://purl.org/rss/1.0/modules/content/"
		  xmlns:dc="http://purl.org/dc/elements/1.1/"
		  xmlns:itunes="http://www.itunes.com/dtds/podcast-1.0.dtd"
		  xmlns:podcast="https://podcastindex.org/namespace/1.0"
		>
			<channel>
				<title>The title of this Podcast</title>
				<copyright>All rights reserved</copyright>
				<link>http://example.com</link>
				<itunes:author>The Podcast Author</itunes:author>
				<image>
					<alt>Some image</alt>
					<!-- intentionally skipping <url> -->
				</image>
			</channel>
		</rss>
		';

		$podcastData = PodcastData::parseRssXml($xml, 1337);
		$expected = [
			'title' => 'The title of this Podcast',
			'author' => 'The Podcast Author',
			'link' => 'http://example.com',
			'description' => null,
			'imageUrl' => null,
			'imageBlob' => null,
			'fetchedAtUnix' => 1337,
		];
		$this->assertSame($expected, $podcastData->toArray());
	}
}

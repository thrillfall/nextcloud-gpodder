<?php
declare(strict_types=1);

namespace OCA\GPodderSync\Core\PodcastData;

use DateTime;
use SimpleXMLElement;

class PodcastData {
	private string $title;
	private string $author;
	private string $link;
	private string $description;
	private string $image;
	private int $fetchedAtUnix;

	public function __construct(
		string $title,
		string $author,
		string $link,
		string $description,
		string $image,
		int $fetchedAtUnix,
	) {
		$this->title = $title;
		$this->author = $author;
		$this->link = $link;
		$this->description = $description;
		$this->image = $image;
		$this->fetchedAtUnix = $fetchedAtUnix;
	}

	public static function parseRssXml(string $xmlString, ?int $fetchedAtUnix = null): PodcastData {
		$xml = new SimpleXMLElement($xmlString);
		$channel = $xml->channel;
		return new PodcastData(
			title: (string)$channel->title,
			author: self::getXPathContent($xml, '/rss/channel/itunes:author'),
			link: (string)$channel->link,
			description: (string)$channel->description,
			image:
				self::getXPathContent($xml, '/rss/channel/image/url')
				?? self::getXPathAttribute($xml, '/rss/channel/itunes:image/@href'),
			fetchedAtUnix: $fetchedAtUnix ?? (new DateTime())->getTimestamp(),
		);
	}

	private static function getXPathContent(SimpleXMLElement $xml, string $xpath) {
		$match = $xml->xpath($xpath);
		if ($match) {
			return (string)$match[0];
		}
		return null;
	}

	private static function getXPathAttribute(SimpleXMLElement $xml, string $xpath) {
		$match = $xml->xpath($xpath);
		if ($match) {
			return (string)$match[0][0];
		}
		return null;
	}

	/**
	 * @return string
	 */
	public function getTitle(): string {
		return $this->title;
	}

	/**
	 * @return string
	 */
	public function getAuthor(): string {
		return $this->author;
	}

	/**
	 * @return string
	 */
	public function getLink(): string {
		return $this->link;
	}

	/**
	 * @return string
	 */
	public function getDescription(): string {
		return $this->description;
	}

	/**
	 * @return string
	 */
	public function getImage(): string {
		return $this->image;
	}

	/**
	 * @return int
	 */
	public function getFetchedAtUnix(): int {
		return $this->fetchedAtUnix;
	}

	/**
	 * @return string
	 */
	public function __toString() : String {
		return $this->title;
	}

	/**
	 * @return array
	 */
	public function toArray(): array {
		return
		[
			'title' => $this->title,
			'author' => $this->author,
			'link' => $this->link,
			'description' => $this->description,
			'image' => $this->image,
			'fetchedAtUnix' => $this->fetchedAtUnix,
		];
	}

	/**
	 * @return PodcastData
	 */
	public static function fromArray(array $data): PodcastData {
		return new PodcastData(
			title: $data['title'],
			author: $data['author'],
			link: $data['link'],
			description: $data['description'],
			image: $data['image'],
			fetchedAtUnix: $data['fetchedAtUnix'],
		);
	}
}


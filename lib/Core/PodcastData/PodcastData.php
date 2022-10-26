<?php
declare(strict_types=1);

namespace OCA\GPodderSync\Core\PodcastData;

use DateTime;
use JsonSerializable;
use SimpleXMLElement;

class PodcastData implements JsonSerializable {
	private ?string $title;
	private ?string $author;
	private ?string $link;
	private ?string $description;
	private ?string $imageUrl;
	private int $fetchedAtUnix;
	private ?string $imageBlob;

	public function __construct(
		?string $title,
		?string $author,
		?string $link,
		?string $description,
		?string $imageUrl,
		int $fetchedAtUnix,
		?string $imageBlob = null
	) {
		$this->title = $title;
		$this->author = $author;
		$this->link = $link;
		$this->description = $description;
		$this->imageUrl = $imageUrl;
		$this->fetchedAtUnix = $fetchedAtUnix;
		$this->imageBlob = $imageBlob;
	}

	/**
	 * @return PodcastData
	 * @throws Exception if the XML data could not be parsed.
	 */
	public static function parseRssXml(string $xmlString, ?int $fetchedAtUnix = null): PodcastData {
		$xml = new SimpleXMLElement($xmlString);
		$channel = $xml->channel;
		return new PodcastData(
			title: self::stringOrNull($channel->title),
			author: self::getXPathContent($xml, '/rss/channel/itunes:author'),
			link: self::stringOrNull($channel->link),
			description: self::stringOrNull($channel->description),
			imageUrl:
				self::getXPathContent($xml, '/rss/channel/image/url')
				?? self::getXPathAttribute($xml, '/rss/channel/itunes:image/@href'),
			fetchedAtUnix: $fetchedAtUnix ?? (new DateTime())->getTimestamp(),
		);
	}

	private static function stringOrNull(mixed $value): ?string {
		if ($value) {
			return (string)$value;
		}
		return null;
	}

	private static function getXPathContent(SimpleXMLElement $xml, string $xpath): ?string {
		$match = $xml->xpath($xpath);
		if ($match) {
			return (string)$match[0];
		}
		return null;
	}

	private static function getXPathAttribute(SimpleXMLElement $xml, string $xpath): ?string {
		$match = $xml->xpath($xpath);
		if ($match) {
			return (string)$match[0][0];
		}
		return null;
	}

	/**
	 * @return string|null
	 */
	public function getTitle(): ?string {
		return $this->title;
	}

	/**
	 * @return string|null
	 */
	public function getAuthor(): ?string {
		return $this->author;
	}

	/**
	 * @return string|null
	 */
	public function getLink(): ?string {
		return $this->link;
	}

	/**
	 * @return string|null
	 */
	public function getDescription(): ?string {
		return $this->description;
	}

	/**
	 * @return string|null
	 */
	public function getImageUrl(): ?string {
		return $this->imageUrl;
	}

	/**
	 * @return int|null
	 */
	public function getFetchedAtUnix(): ?int {
		return $this->fetchedAtUnix;
	}

	/**
	 * @return string|null
	 */
	public function getImageBlob(): ?string {
		return $this->imageBlob;
	}

	/**
	 * @param string $blob
	 * @return void
	 */
	public function setImageBlob(?string $blob): void {
		$this->imageBlob = $blob;
	}

	/**
	 * @return string
	 */
	public function __toString() : string {
		return $this->title ?? '/no title/';
	}

	/**
	 * @return array<string,mixed>
	 */
	public function toArray(): array {
		return
		[
			'title' => $this->title,
			'author' => $this->author,
			'link' => $this->link,
			'description' => $this->description,
			'imageUrl' => $this->imageUrl,
			'imageBlob' => $this->imageBlob,
			'fetchedAtUnix' => $this->fetchedAtUnix,
		];
	}

	/**
	 * @return array<string,mixed>
	 */
	public function jsonSerialize(): mixed {
		return $this->toArray();
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
			imageUrl: $data['imageUrl'],
			fetchedAtUnix: $data['fetchedAtUnix'],
			imageBlob: $data['imageBlob'],
		);
	}
}


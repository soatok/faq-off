<?php
declare(strict_types=1);
namespace Soatok\FaqOff\Splices;

use Interop\Container\Exception\ContainerException;
use League\CommonMark\CommonMarkConverter;
use Slim\Container;
use Soatok\AnthroKit\Splice;

/**
 * Class Notices
 * @package Soatok\FaqOff\Splices
 */
class Notices extends Splice
{
    /** @var CommonMarkConverter $converter */
    protected $converter;

    /** @var \HTMLPurifier $purifier */
    protected $purifier;

    /**
     * Notices constructor. OwO what's this?
     *
     * @param Container $container
     * @throws ContainerException
     */
    public function __construct(Container $container)
    {
        parent::__construct($container);

        /** @var CommonMarkConverter $converter */
        $converter = $container['markdown'];
        $this->converter = $converter;

        /** @var \HTMLPurifier $purifier */
        $purifier = $container['purifier'];
        $this->purifier = $purifier;
    }

    /**
     * @param string $title
     * @param string $contents
     * @param int $accountId
     * @return int|null
     * @throws \Exception
     */
    public function create(string $title, string $contents, int $accountId): ?int
    {
        $this->db->beginTransaction();
        $noticeId = (int) $this->db->insertGet(
            'faqoff_frontpage_notices',
            [
                'headline' => $title,
                'body' => $contents,
                'account_id' => $accountId
            ],
            'noticeid'
        );
        if (!$this->db->commit()) {
            return null;
        }
        return $noticeId;
    }

    /**
     * @return array
     */
    public function getAll(): array
    {
        $news = $this->db->run(
            "SELECT noticeid, headline, body
             FROM faqoff_frontpage_notices 
             ORDER BY created DESC"
        );
        if (!$news) {
            // Good news
            return [];
        }
        return $news;
    }

    /**
     * @param int $id
     * @return array
     */
    public function getById(int $id): array
    {
        $news = $this->db->row(
            "SELECT * FROM faqoff_frontpage_notices WHERE noticeid = ?",
            $id
        );
        if (!$news) {
            return [];
        }
        return $news;
    }

    /**
     * @param int $limit
     * @param int $offset
     * @return array
     */
    public function getRecent(int $limit = 5, int $offset = 0): array
    {
        if ($limit < 1) {
            return [];
        }
        $news = $this->db->run(
            "SELECT * FROM faqoff_frontpage_notices 
             ORDER BY created DESC
             OFFSET {$offset} LIMIT {$limit}"
        );
        if (!$news) {
            // Good news
            return [];
        }

        foreach ($news as $i => $row) {
            $news[$i]['body'] = $this->purifier->purify(
                $this->converter->convertToHtml($row['body'])
            );
        }
        return $news;
    }

    /**
     * @param int $id
     * @param string $title
     * @param string $contents
     * @param int $accountId
     * @return bool
     */
    public function update(int $id, string $title, string $contents, int $accountId): bool
    {
        $this->db->beginTransaction();
        $this->db->update(
            'faqoff_frontpage_notices',
            [
                'headline' => $title,
                'body' => $contents,
                'account_id' => $accountId
            ],
            [
                'noticeid' => $id
            ]
        );
        return $this->db->commit();
    }
}

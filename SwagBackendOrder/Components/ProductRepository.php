<?php
/**
 * (c) shopware AG <info@shopware.com>
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */

namespace SwagBackendOrder\Components;

use Shopware\Components\Model\ModelManager;
use Shopware\Models\Article\Article;
use Shopware\Models\Article\Supplier;
use Doctrine\ORM\Query\Expr;

class ProductRepository
{
    /**
     * @var ModelManager
     */
    private $modelManager;

    /**
     * @param ModelManager $modelManager
     */
    public function __construct(ModelManager $modelManager)
    {
        $this->modelManager = $modelManager;
    }

    /**
     * @param string $search
     * @param string $groupKey
     * @return \Doctrine\ORM\QueryBuilder|\Shopware\Components\Model\QueryBuilder
     */
    public function getProductQueryBuilder($search, $groupKey = 'EK')
    {
        $builder = $this->modelManager->createQueryBuilder();

        /**
         * query to search for article variants or the article ordernumber
         * the query concats the article name and the additional text field for the search
         */
        $builder->select(
            'articles.id AS articleId,
            details.number,
            articles.name,
            details.id,
            details.inStock,
            articles.taxId,
            prices.price,
            details.additionalText,
            tax.tax,
            articles.supplierId,
            sp.id as supplierID'
        );

        $builder->from(Article::class, 'articles')
            ->leftJoin('articles.details', 'details')
            ->leftJoin('details.prices', 'prices')
            ->leftJoin('articles.tax', 'tax')
            ->leftJoin(
                Supplier::class,
                'sp',
                Expr\Join::WITH,
                'articles.supplierId = sp.id'
            )
            ->where('articles.name LIKE :number')
            ->orWhere('details.number LIKE :number')
            ->orWhere('sp.name LIKE :number')
            ->andWhere('prices.customerGroupKey = :groupkey')
            ->setParameter('number', $search)
            ->setParameter('groupkey', $groupKey)
            ->orderBy('details.number')
            ->groupBy('details.number')
            ->setMaxResults(12);

        return $builder;
    }
}
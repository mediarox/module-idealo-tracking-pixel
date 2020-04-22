<?php
/**
 * @package   Mediarox_IdealoTrackingPixel
 * @copyright Copyright 2020 (c) mediarox UG (haftungsbeschraenkt) (http://www.mediarox.de)
 * @author    Marcus Bernt <mbernt@mediarox.de>
 */

namespace Mediarox\IdealoTrackingPixel\Model\Config\Source;

use Magento\Catalog\Model\Product;
use Magento\Eav\Api\AttributeRepositoryInterface;
use Magento\Framework\Api\SearchCriteriaBuilder;
use Magento\Framework\Data\OptionSourceInterface;

/**
 * Class ManufacturerOptions
 */
class ManufacturerOptions implements OptionSourceInterface
{
    /**
     * @var AttributeRepositoryInterface
     */
    private $attributeRepository;
    /**
     * @var SearchCriteriaBuilder
     */
    private $criteriaBuilder;

    /**
     * ManufacturerOptions constructor.
     *
     * @param AttributeRepositoryInterface $attributeRepository
     * @param SearchCriteriaBuilder        $criteriaBuilder
     */
    public function __construct(
        AttributeRepositoryInterface $attributeRepository,
        SearchCriteriaBuilder $criteriaBuilder
    ) {
        $this->attributeRepository = $attributeRepository;
        $this->criteriaBuilder = $criteriaBuilder;
    }

    /**
     * @inheritDoc
     */
    public function toOptionArray()
    {
        $attributes = $this->attributeRepository->getList(Product::ENTITY, $this->criteriaBuilder->create());
        $options = [];

        foreach ($attributes->getItems() as $attribute) {
            if ($label = $attribute->getDefaultFrontendLabel()) {
                $options[] = [
                    'value' => $attribute->getAttributeCode(),
                    'label' => $label
                ];
            }
        }
        return $options;
    }
}

<?php
/**
 * Shopware 5
 * Copyright (c) shopware AG
 *
 * According to our dual licensing model, this program can be used either
 * under the terms of the GNU Affero General Public License, version 3,
 * or under a proprietary license.
 *
 * The texts of the GNU Affero General Public License with an additional
 * permission and of our proprietary license can be found at and
 * in the LICENSE file you have received along with this program.
 *
 * This program is distributed in the hope that it will be useful,
 * but WITHOUT ANY WARRANTY; without even the implied warranty of
 * MERCHANTABILITY or FITNESS FOR A PARTICULAR PURPOSE. See the
 * GNU Affero General Public License for more details.
 *
 * "Shopware" is a registered trademark of shopware AG.
 * The licensing of the program under the AGPLv3 does not imply a
 * trademark license. Therefore any rights, title and interest in
 * our trademarks remain entirely with us.
 */

namespace Shopware\Components\Model;

use Doctrine\Common\Collections\ArrayCollection;

/**
 * Abstract class for shopware standard models.
 */
abstract class ModelEntity
{
    /**
     * Example:
     *
     * $model->fromArray($data);
     * $model->setShipping($shippingModel->fromArray($shippingData));
     *
     * @param array $fillable optional property whitelist for mass-assignment
     *
     * @return $this
     */
    public function fromArray(array $array = [], array $fillable = [])
    {
        foreach ($array as $key => $value) {
            if (count($fillable) && !in_array($key, $fillable)) {
                continue;
            }

            $method = 'set' . ucfirst($key);
            if (method_exists($this, $method)) {
                $this->$method($value);
            }
        }

        return $this;
    }

    /**
     * Helper function to set the association data of a ORM\OneToOne association of doctrine.
     * <br><br>
     * The <b>$data</b> parameter contains the data for the property. It can contains an array with model data
     * or and instance of the expected model. If the $data parameter is set to null the associated model
     * will removed.
     * <br><br>
     * The <b>$model</b> parameter expects the full name of the associated model.
     * For example:
     * <ul>
     * <li>We are in the Customer model in the setBilling() function.
     * <li>Here we want to set the Billing object over the "setOneToOne" function
     * <li>So we passed as $model parameter: <b>"\Shopware\Models\Customer\Billing"</b>
     * </ul>
     * <br>
     * The <b>$property</b> parameter expect the name of the association property.
     * For example:
     * <ul>
     * <li>In the setBilling() function of the customer model we would expects <b>"billing"</b>.</li>
     * </ul>
     * <br>
     * The <b>$reference</b> property expect the name of the property on the other side of the association.
     * For example:
     * <ul>
     * <li>In the setBilling() function we want to fill the billing data.</li>
     * <li>To set the reference between customer and billing we set in the billing object the "customer"</li>
     * <li>To set the customer we use the "$billing->setCustomer()" function.</li>
     * <li>So the parameter expect <b>"customer"</b></li>
     * </ul>
     *
     * @param ModelEntity|ArrayCollection|array|null $data      Model data, example: an instance of \Shopware\Models\Order\Order
     * @param string                                 $model     Full namespace of the association model, example: '\Shopware\Models\Order\Order'
     * @param string                                 $property  Name of the association property, example: 'orders'
     * @param string|null                            $reference Name of the reference property, example: 'customer'
     *
     * @return $this
     */
    public function setOneToOne($data, $model, $property, $reference = null)
    {
        $getterFunction = 'get' . ucfirst($property);
        $setterFunction = ($reference !== null) ? 'set' . ucfirst($reference) : false;

        $this->$getterFunction();

        // If an expected instance passed, set this in the internal property
        if ($data instanceof $model) {
            $this->$property = $data;
            if ($setterFunction) {
                $this->$property->$setterFunction($this);
            }

            return $this;
        }

        // Check if expected model already exists but null passed, than clear the association.
        if ($data === null && $this->$getterFunction()) {
            if ($setterFunction) {
                $this->$property->$setterFunction(null);
            }
            $this->$property = null;

            return $this;
        }

        // If the parameter is no array, return
        if (!is_array($data) || empty($data)) {
            return $this;
        }

        // Check if the model association isn't created
        if ($this->$getterFunction() === null) {
            $this->$property = new $model();
        }

        // Load array data into the object and set association reference.
        $this->$property->fromArray($data);
        if ($setterFunction) {
            $this->$property->$setterFunction($this);
        }

        return $this;
    }

    /**
     * Helper function to set the association data of a ORM\OneToMany association of doctrine.
     * <br><br>
     * The <b>$data</b> parameter contains the data for the collection property. It can contains an array of
     * models or data arrays. If the $data parameter is set to null the associated collection will cleared.
     * <br><br>
     * The <b>$model</b> parameter expects the full name of the associated model.
     * For example:
     * <ul>
     * <li>We are in the Customer model in the setOrders() function.</li>
     * <li>Here we want to set the Order objects over the "setOneToMany" function</li>
     * <li>So we passed as $model parameter: <b>"\Shopware\Models\Order\Order"</b></li>
     * </ul>
     * <br>
     * The <b>$property</b> parameter expect the name of the association property.
     * For example:
     * <ul>
     * <li>In the setOrders() function of the customer model we would expects <b>"orders"</b>.</li>
     * </ul>
     * <br>
     * The <b>$reference</b> property expect the name of the property on the other side of the association.
     * For example:
     * <ul>
     * <li>In the setOrders() function we want to fill the orders data.</li>
     * <li>To set the reference between customer and orders we set in the orders object the "customer"</li>
     * <li>To set the customer we use the "$order->setCustomer()" function.</li>
     * <li>So the parameter expect <b>"customer"</b></li>
     * </ul>
     *
     * @param array[]|ModelEntity[]|iterable|null $data      Model data, example: an array of \Shopware\Models\Order\Order
     * @param string                              $model     Full namespace of the association model, example: '\Shopware\Models\Order\Order'
     * @param string                              $property  Name of the association property, example: 'orders'
     * @param string                              $reference Name of the reference property, example: 'customer'
     *
     * @return $this
     */
    public function setOneToMany($data, $model, $property, $reference = null)
    {
        $getterFunction = 'get' . ucfirst($property);
        $setterFunction = null;
        if ($reference !== null) {
            $setterFunction = 'set' . ucfirst($reference);
        }

        // To remove the whole one to many association, u can pass null as parameter.
        if ($data === null) {
            $this->$getterFunction()->clear();

            return $this;
        }
        // If no iterable passed or if false passed, return
        if (!is_iterable($data)) {
            return $this;
        }

        // Create a new collection to collect all updated and created models.
        $updated = new ArrayCollection();

        // Iterate all passed items
        /** @var array[]|ModelEntity[]|ArrayCollection<ModelEntity> $data */
        foreach ($data as $item) {
            // To get the right collection item use the internal helper function
            if (is_array($item) && isset($item['id']) && $item['id'] !== null) {
                $attribute = $this->getArrayCollectionElementById($this->$getterFunction(), $item['id']);
                if (!$attribute instanceof $model) {
                    $attribute = new $model();
                }
                // If the item is an array without an id, create a new model.
            } elseif (is_array($item)) {
                $attribute = new $model();
            // If the item is no array, it could be an instance of the expected object.
            } else {
                $attribute = $item;
            }

            // Check if the object correctly initialed. If this is not the case continue.
            if (!$attribute instanceof $model) {
                continue;
            }

            // If the current item is an array, use the from array function to set the data.
            if (is_array($item)) {
                $attribute->fromArray($item);
            }

            // After the attribute filled with data, set the association reference and add the model to the internal collection.
            if ($setterFunction !== null) {
                $attribute->$setterFunction($this);
            }

            if (!$this->$getterFunction()->contains($attribute)) {
                $this->$getterFunction()->add($attribute);
            }

            // Add the model to the updated collection to have an flag which models updated.
            $updated->add($attribute);
        }

        // After all passed data items added to the internal collection, we have to iterate the items
        // to remove all old items which are not updated.
        foreach ($this->$getterFunction() as $attr) {
            // The updated collection contains all updated and created models.
            if (!$updated->contains($attr)) {
                $this->$getterFunction()->removeElement($attr);
            }
        }

        return $this;
    }

    /**
     * Helper function to set the association data of a ORM\ManyToOne association of doctrine.
     * <br><br>
     * The <b>$data</b> parameter contains the data for the collection property. It can contains an array of
     * models or data arrays. If the $data parameter is set to null the associated collection will cleared.
     * <br><br>
     * The <b>$model</b> parameter expects the full name of the associated model.
     * For example:
     * <ul>
     * <li>We are in the Article model in the setSupplier() function.</li>
     * <li>Here we want to set the Supplier objects over the "setManyToOne" function</li>
     * <li>So we passed as $model parameter: <b>"\Shopware\Models\Article\Supplier"</b></li>
     * </ul>
     * <br>
     * The <b>$property</b> parameter expect the name of the association property.
     * For example:
     * <ul>
     * <li>In the setSupplier() function of the article model we would expects <b>"supplier"</b>.</li>
     * </ul>
     *
     * @param ModelEntity|array|null $data     Model data, example: an data array or an instance of the model
     * @param string                 $model    Full namespace of the association model, example: '\Shopware\Models\Article\Supplier'
     * @param string                 $property Name of the association property, example: 'supplier'
     *
     * @throws \InvalidArgumentException
     *
     * @return $this
     */
    public function setManyToOne($data, $model, $property)
    {
        $getterFunction = 'get' . ucfirst($property);
        $this->$getterFunction();

        // If an expected instance passed, set this in the internal property
        if ($data instanceof $model) {
            $this->$property = $data;

            return $this;
        }

        // Check if expected model already exists but null passed, than clear the association.
        if ($data === null && $this->$getterFunction()) {
            $this->$property = null;

            return $this;
        }

        // If the parameter is no array, return
        if (!is_array($data) || empty($data)) {
            return $this;
        }

        // Check if the model association isn't created
        $instance = $this->$getterFunction();
        if ($instance === null) {
            $instance = new $model();
        }

        $id = $instance->getId();

        // If an id passed, the already assigned model has an id and the ids are not equal, we can't update the model instance.
        // Otherwise we would update the instance with the id 1 with the data for the instance with id 2.
        if (!empty($data['id']) && !empty($id) && $data['id'] !== $id) {
            throw new \InvalidArgumentException('Passed id and id of the already assigned model are not equal');
        }

        $instance->fromArray($data);
        $this->$property = $instance;

        return $this;
    }

    /**
     * @param ModelEntity[]|ArrayCollection<ModelEntity> $collection
     * @param int                                        $id
     *
     * @return ModelEntity|null
     */
    private function getArrayCollectionElementById($collection, $id)
    {
        if ($collection->count() === 0) {
            return null;
        }

        foreach ($collection as $item) {
            if ($item->getId() === $id) {
                return $item;
            }
        }

        return null;
    }
}

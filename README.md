IbrowsSonataAdminAnnotationBundle 
============================

Manage Sonata Form, Data, List and ShowMapper over annotations

[![Latest Stable Version](https://poser.pugx.org/ibrows/sonata-admin-annotation-bundle/v/stable)](https://packagist.org/packages/ibrows/sonata-admin-annotation-bundle) [![Total Downloads](https://poser.pugx.org/ibrows/sonata-admin-annotation-bundle/downloads)](https://packagist.org/packages/ibrows/sonata-admin-annotation-bundle)

[![knpbundles.com](http://knpbundles.com/ibrows/IbrowsSonataAdminAnnotationBundle/badge-short)](http://knpbundles.com/ibrows/IbrowsSonataAdminAnnotationBundle)

How to install
==============

### Add Bundle to your composer.json

```js
// composer.json

{
    "require": {
        "ibrows/sonata-admin-annotation-bundle": "*"
    }
}
```

### Install the bundle from console with composer.phar

``` bash
$ php composer.phar update ibrows/sonata-admin-annotation-bundle
```

### Enable the bundle in AppKernel.php - Dont forget to give AppKernel to the Bundle and register the Bundle *BEFORE* SonataAdminBundle

``` php
<?php
// app/AppKernel.php

public function registerBundles()
{
    $bundles = array(
        // ...
        new Ibrows\Bundle\SonataAdminAnnotationBundle\IbrowsSonataAdminAnnotationBundle($this),
    );
}
```

### Configuration (Only needed if @AutoService is used)

``` yaml
ibrows_sonata_admin_annotation:
    autoservice:
        service_id_prefix: companyname.admin
        default_entity:
            admin: CompanyName\ProjectNameBundle\Admin\DefaultAdmin
            controller: CompanyNameProjectNameBundle:Admin/DefaultAdmin
        entities:
            - {directory: %kernel.root_dir%/../src/CompanyName/ProjectNameBundle/Entity, prefix: CompanyName\ProjectNameBundle\Entity}
```

### The Annotations

- Ibrows\Bundle\SonataAdminAnnotationBundle\Annotation\Order on classes for global orders like "show me all properties"
- Ibrows\Bundle\SonataAdminAnnotationBundle\Annotation on properties for specific configurations and excludes if orders are used

> If any FormMapperExclude Annotation is found on a property the reader assumes that there es an Order/FormMapperAll on the class (same goes for the other annotations - List/Form/Datagrid)

Have a look on the Annotations to see what options they accept

### Example

``` php
<?php

namespace YourApp\Entity;

use Ibrows\Bundle\SonataAdminAnnotationBundle\Annotation as Sonata;

use Doctrine\ORM\Mapping as ORM;
use Doctrine\Common\Collections\Collection;
use Doctrine\Common\Collections\ArrayCollection;

/**
 * @ORM\Entity
 * @Sonata\Order\ListMapperAll
 * @Sonata\Order\ShowMapperAll
 * @Sonata\Order\FormMapperAll
 *
 * @Sonata\Order\ShowAndFormreorder(with="General", keys={"name"})
 * @Sonata\Order\ShowAndFormreorder(keys={"taxRate"})
 *
 * @Sonata\AutoService()
 */
class Country
{
    /**
     * @var integer $id
     * @ORM\Column(type="integer")
     * @ORM\Id
     * @ORM\GeneratedValue(strategy="AUTO")
     * @Sonata\ListMapper(identifier=true)
     * @Sonata\Order\FormMapperExclude
     * @Sonata\ShowMapper(with="General")
     */
    protected $id;

    /**
     * @var string $name
     * @ORM\Column(type="string")
     * @Sonata\ShowMapper(with="General")
     */
    protected $name;

    /**
     * @var float
     * @ORM\Column(type="float", name="shipping_free_limit")
     */
    protected $shippingFreeLimit;

    /**
     * @var float
     * @ORM\Column(type="float", name="shipping_fixed_rate")
     */
    protected $shippingFixedRate;

    /**
     * @var float
     * @ORM\Column(type="float", name="tax_rate")
     */
    protected $taxRate;

    /**
     * @ORM\ManyToMany(targetEntity="Article", inversedBy="countries")
     * @ORM\JoinTable(name="article_country")
     * @Sonata\Order\ListMapperExclude
     * @Sonata\FormMapper(options={"required"=false})
     **/
    protected $articles;
}
```

``` php
<?php

namespace YourApp\Entity;

use Ibrows\Bundle\SonataAdminAnnotationBundle\Annotation as Sonata;

use Sonata\AdminBundle\Form\FormMapper;
use Sonata\AdminBundle\Show\ShowMapper;
use Sonata\AdminBundle\Datagrid\DatagridMapper;
use Sonata\AdminBundle\Datagrid\ListMapper;

use Application\Sonata\MediaBundle\Entity\Gallery;

use Doctrine\ORM\Mapping as ORM;
use Doctrine\Common\Collections\Collection;
use Doctrine\Common\Collections\ArrayCollection;

/**
 * @ORM\Entity
 * @Sonata\Order\ShowAndFormreorder(keys={"name"})
 */
class Article
{
    /**
     * @var string $number
     * @ORM\Column(name="number", type="string", unique=true)
     */
    protected $number;

    /**
     * @var string $name
     * @ORM\Column(type="string")
     * @Sonata\DatagridMapper
     */
    protected $name;

    /**
     * @var string $description
     * @ORM\Column(type="text")
     * @Sonata\Order\ListMapperExclude
     */
    protected $description;

    /**
     * @var string $matchCode
     * @ORM\Column(name="match_code", type="string")
     * @Sonata\Order\ListMapperExclude
     */
    protected $matchCode;

    /**
     * @var string $articleGroup
     * @ORM\Column(name="article_group", type="string")
     * @Sonata\Order\ListMapperExclude
     * @Sonata\Order\ShowMapperExclude
     */
    protected $articleGroup;

    /**
     * @var ArrayCollection
     * @ORM\OneToMany(targetEntity="ArticlePrice", mappedBy="articleEntity")
     * @Sonata\Order\ListMapperExclude
     */
    protected $prices;

    /**
     * @ORM\ManyToMany(targetEntity="Country", mappedBy="articles")
     **/
    protected $countries;

    /**
     * @var Gallery $pictures
     * @ORM\ManyToOne(
     *      targetEntity="Application\Sonata\MediaBundle\Entity\Gallery",
     *      cascade={"persist"}
     * )
     * @Sonata\FormMapper(
     *      type="sonata_type_model_list",
     *      options={"required"=false},
     *      fieldDescriptionOptions={
     *          "link_parameters"={
     *              "context":"article",
     *              "provider":"sonata.media.provider.image"
     *          }
     *      }
     * )
     */
    protected $pictures;

    /**
     * @param \Sonata\AdminBundle\Form\FormMapper $formMapper
     * @Sonata\FormCallback
     */
    public static function configureFormFields(FormMapper $formMapper)
    {
        $formMapper->add('countries');
    }
}
```

### Using in the Admin (maybe in an AbstractAdmin.php)

#### With the AbstractSonataAdminAnnotationAdmin

```php
<?php

namespace YourApp\Admin;

use Ibrows\Bundle\SonataAdminAnnotationBundle\Admin\AbstractSonataAdminAnnotationAdmin;

abstract class AbstractAdmin extends AbstractSonataAdminAnnotationAdmin
{

}
```

#### With Traits (PHP >=5.4)
```php
<?php

namespace YourApp\Admin;

use Ibrows\Bundle\SonataAdminAnnotationBundle\Admin\SonataAdminAnnotationAllTrait;

use Sonata\AdminBundle\Admin\Admin;

abstract class AbstractAdmin extends Admin
{
    use SonataAdminAnnotationAllTrait;
}
```

#### Own implementation

``` php
<?php

namespace YourApp\Admin;

use Ibrows\Bundle\SonataAdminAnnotationBundle\Reader\SonataAdminAnnotationReaderInterface;

use Symfony\Component\DependencyInjection\ContainerInterface;

use Sonata\AdminBundle\Admin\Admin;

use Sonata\AdminBundle\Datagrid\ListMapper;
use Sonata\AdminBundle\Datagrid\DatagridMapper;
use Sonata\AdminBundle\Form\FormMapper;
use Sonata\AdminBundle\Show\ShowMapper;

abstract class AbstractAdmin extends Admin
{
    /**
     * @param ListMapper $listMapper
     */
    protected function configureListFields(ListMapper $listMapper)
    {
        $this->getSonataAnnotationReader()->configureListFields($this->getClass(), $listMapper);
    }

    /**
     * @param FormMapper $formMapper
     */
    protected function configureFormFields(FormMapper $formMapper)
    {
        $this->getSonataAnnotationReader()->configureFormFields($this->getClass(), $formMapper);
    }

    /**
     * @param ShowMapper $showMapper
     */
    protected function configureShowFields(ShowMapper $showMapper)
    {
        $this->getSonataAnnotationReader()->configureShowFields($this->getClass(), $showMapper);
    }

    /**
     * @param DatagridMapper $datagridMapper
     */
    protected function configureDatagridFilters(DatagridMapper $datagridMapper)
    {
        $this->getSonataAnnotationReader()->configureDatagridFilters($this->getClass(), $datagridMapper);
    }

    /**
     * @return ContainerInterface
     */
    protected function getContainer()
    {
        return $this->getConfigurationPool()->getContainer();
    }

    /**
     * @return SonataAdminAnnotationReaderInterface
     */
    protected function getSonataAnnotationReader()
    {
        return $this->getContainer()->get('ibrows_sonataannotation.reader');
    }
}
```

Known issues
============
### Version 2.2.*

- When using the @AutoService Annotation you have to clear the cache everytime you make changes on this Annotation (remove/add/edit). Reason is that the appDevDebugContainer is fully cached and the CompilerPass cannot register the new changes.

### Version 1.1.*

- Using oneToMany/manyToOne relations produces an "entity not managed" doctrine error. Reason is that FormMapper-Annotation per default sets by_reference to false, using @FormMapper(options={"by_reference"=true}) will fix that.

New features
============

### Version 2.4

- New "tab" and "tabOptions" in FormMapper and ShowMapper Annotation for grouping -> @FormMapper(tab="Main")

### Version 2.2

- Allow to register SonataAdminServices over Annotations with @AutoService @see Known issues for caching problems

### Version 1.2

- Allow reorder of FormMapper/ShowMapper and ListMapper with @Order/FormReorder, @Order/ShowReorder, @Order/ListReorder or @Order/ShowAndFormReorder annotations

### Version 1.1

- New "with" and "withOptions" in FormMapper and ShowMapper Annotation for grouping -> @FormMapper(with="Main")
- Allow configuration of static callback methods in entity with @FormCallback on Method (see @FormCallback example)

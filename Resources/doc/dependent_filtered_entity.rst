ShtumiUsefulBundle - make typical things easier

Dependent filtered entity
=========================

.. image:: https://github.com/shtumi/ShtumiUsefulBundle/raw/master/Resources/doc/images/dependent_filtered_entity.png


Configuration
-------------

You should configure relationship between master and dependent fields for each pair:

*In this example master entity - AcmeDemoBundle:Country, dependent - AcmeDemoBundle:Region*

// app/config/config.yml

::

    shtumi_useful :
        dependent_filtered_entities:
            region_by_country:
                class: AcmeDemoBundle:Region
                parent_property: country
                property: title
                callback: filterBySomeField
                role: ROLE_USER
                no_result_msg: 'No regions found for that country'
                order_property: title
                order_direction: ASC

- **class** - Doctrine dependent entity.
- **role** - User role to use form type. Default: ``IS_AUTHENTICATED_ANONYMOUSLY``. It needs for security reason.
- **parent_property** - property that contains master entity with ManyToOne relationship
- **property** - Property that will be used as text in select box. Default: ``title``
- **callback** - Entity repository method name or FQCN and static method name which gets ``Doctrine\ORM\QueryBuilder`` instance and entity root alias string as parameters and returns modified ``QueryBuilder`` object. For example: ``someFilterMethod``, ``SomeClass::someStaticFilterMethod``. Can be callable array too.
- **no_result_msg** - text that will be used for select box where nothing dependent entities were found for selected master entity. Default ``No results were found``. You can translate this message in ``messages.{locale}.php`` files.
- **order_property** - property that used for ordering dependent entities in selec box. Default: ``id``
- **order_direction** - You can use:
   - ``ASC`` - (**default**)
   - ``DESC`` - LIKE '%value'


Usage
=====

Simple usage
------------

Master and dependent fields should be in form together.

::

    $formBuilder
        ->add(
            'country',
            'entity',
             [
                 'class'      => 'AcmeDemoBundle:Country',
                 'required'   => true,
                 'empty_value'=> '== Choose country ==',
             ]
         )
        ->add(
            'region',
            'shtumi_dependent_filtered_entity',
            [
                'entity_alias' => 'region_by_country',
                'empty_value'=> '== Choose region ==',
                'parent_field'=>'country',
            ]
        )
    ;

- **parent_field** - name of master field in your FormBuilder

Default options for Select2 fields
----------------------------------

::

    $formBuilder
        ->add(
            'country',
            'entity',
             [
                 'class'      => 'AcmeDemoBundle:Country',
                 'required'   => true,
                 'empty_value'=> '== Choose country ==',
             ]
         )
        ->add(
            'region',
            'shtumi_dependent_filtered_select2',
            [
                'entity_alias' => 'region_by_country',
                'empty_value'=> '== Choose region ==',
                'parent_field'=>'country',
                'preferred_value' => $region->getId(),
                'preferred_text' => $region->getName(),
            ]
        )
    ;

- **parent_field** - name of master field in your FormBuilder
- **preferred_value** - (optional) Value of option which will be used as default until user chose any other option. Must be used with **preferred_text**
- **preferred_text** - (optional) Text of option which will be used as default until user chose any other option. Must be used with **preferred_value**

Callback example
----------------------------------------------------------------------------------------------------

::

    # /app/config/config.yml
    shtumi_useful :
        dependent_filtered_entities:
            region_by_country:
                class: AcmeDemoBundle:Region
                parent_property: country
                property: title
                callback: filterByRemoved

If you using repository method in ``callback`` parameter (like "``filterByRemoved``") you must add this method to your entity repository:

::

    // \Vendor\Namespace\Repository\SomeEntityRepository
    public function filterByRemoved(Doctrine\ORM\QueryBuilder $qb, $alias)
    {
        $qb->andWhere($alias.'.isRemoved <> TRUE');
        return $qb;
    }

Or if you using FQCN with static method name (like "``SomeClass::filterByRemoved``") you must add static method:

::

    // \Vendor\Namespace\SomeClassWithStaticMethod
    public static function filterByRemoved(Doctrine\ORM\QueryBuilder $qb, $alias)
    {
        $qb->andWhere($alias.'.isRemoved <> TRUE');
        return $qb;
    }

Multiple levels
--------------

You can configure multiple dependent filters:

// app/config/config.yml

::

    shtumi_useful :
        dependent_filtered_entities:
            region_by_country:
                class: AcmeDemoBundle:Region
                parent_property: country
                property: title
                role: ROLE_USER
                no_result_msg: 'No regions found for that country'
                order_property: title
                order_direction: ASC
            town_by_region:
                class: AcmeDemoBundle:Town
                parent_property: region
                property: title
                role: ROLE_USER
                no_result_msg: 'No towns found for that region'
                order_property: title
                order_direction: ASC

::

    $formBuilder
        ->add(
            'country',
             'entity',
              [
                'required' => true,
                'empty_value' => '== Choose country =='
              ]
        )
        ->add(
            'region',
            'shtumi_dependent_filtered_entity',
            [
                'entity_alias' => 'region_by_country',
                'empty_value' => '== Choose region ==',
                'parent_field' =>'country'
            ]
        )
        ->add(
            'town',
            'shtumi_dependent_filtered_entity',
            [
                'entity_alias' => 'town_by_region',
                'empty_value' => '== Choose town ==',
                'parent_field' =>'region'
            ]
        )

- **parent_field** - name of master field in your FormBuilder

Sonata_type_model_autocomplete example
--------------
In this example we have a sonata_type_model_autocomplete field 'basis', where we can select multiple bases. These bases
have an owner and this owner can have different priorities. What we want is instead of all priorities only the
priorities related to the owners of the selected bases. We then want to return a slightly altered name of the priority.
This is the `HrName`, defined as getHrName() in Priority entity.

```  $formBuilder
        ->add('basis', 'sonata_type_model_autocomplete', array(
                        'property' => 'articleLong',
                        'multiple' => true
        ))
        ->add('priority', 'shtumi_dependent_filtered_select2', array(
            'entity_alias' => 'prio_by_customer',
            'empty_value'=> 'Select priority',
            'parent_field'=>'basis',
            ))
    ;
```

In `config.yml` :
```
shtumi_useful :
    dependent_filtered_entities:
        prio_by_customer:
            class: CMS3CoreBundle:Priority
            parent_property: companies
            many_to_many: true
            property: name
            result_property: HrName
            no_result_msg: "No results"
            table_name: ACMEDemoBundle>:Basis
            column_name: owner
```

- **parent_property** - the many-to-many field name in the child-entity referring to the parent mentioned in the
`column_name`.
- **table_name** - name of the table where we can find the `column_name`
- **column_name**

You should load `JQuery <http://jquery.com>`_ to your views.
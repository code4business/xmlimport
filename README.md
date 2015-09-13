XML Product Import
==============
Converts XML import files into data that is then imported using [AvS\_FastSimpleImport](https://github.com/avstudnitz/AvS_FastSimpleImport)

Information
-----------
- Version: 0.1.0
- [Github](https://github.com/code4business/xmlimport)

Description
-----------
This is a product importer that transforms product data from XML files into data array compatible with [AvS\_FastSimpleImport](https://github.com/avstudnitz/AvS_FastSimpleImport)
and uses it to import that data. The importer processes all XML files from "inbox" directory and moves them to an error or success directory, depending on import result. Any errors
encountered during import are sent by email.  

Any categories that are in the import file but do not yet exist in the shop can be automatically created.  
Missing attributes can also be created but they will have to be put into apropriate attribute sets manually. Since there is no way to tell what type the data 
should be, all missing attributes are created as select type.

Features
--------
- Import any product type
- Create missing categories
- Create missing attributes
- Send Email with missing attributes
- Send Email with errors encountered

XML format
----------
This sample XML-file explanis the basic structure:
```xml
<?xml version="1.0" encoding="UTF-8"?>
<products>
  <product>  
    <stores>
    <!-- Default scope  -->
      <item>default</item>
      <!-- Possible store scope value  -->
      <item>STORE_CODE</item>
    </stores>
    <!-- Complex attributes: attributes with multiple values 
         (categories, media images, related products)  -->
    <complex_data>
    <!-- Complex Attribute. All <item>s in this <enum> need to 
         have SAME children names and children count  -->
      <enum>
        <!-- Complex Attribute value 1  -->
        <item>
          <!-- Complex Attribute value 1 data (ex: media_image)  -->
          <ATTRIBUTE_CODE>value</ATTRIBUTE_CODE>
          <!-- Complex Attribute value 1 data (ex: media_lable) -->
          <ATTRIBUTE_CODE2>value</ATTRIBUTE_CODE2>
        </item>
        <!-- Complex Attribute value 1  -->
        <item>
          <!-- Complex Attribute value 2 data ex: media_image) -->
          <ATTRIBUTE_CODE>value</ATTRIBUTE_CODE>
          <!-- Complex Attribute value 2 data (ex: media_lable)  -->
          <ATTRIBUTE_CODE2>value</ATTRIBUTE_CODE2>
        </item>
      </enum>
    </complex_data>
    <simple_data>
      <SIMPLE_ATTRIBUTE_CODE>
        <!-- Default scope value  -->
        <default>default scope value</default>
        <!-- store scope value for store: STORE_CODE. Needs to be defined in <stores>  -->
        <STORE_CODE>store scope value</STORE_CODE>
      </SIMPLE_ATTRIBUTE_CODE>
      </simple_data>
  </product>
</products>
```

This are the meanings of the XML nodes:  
- **stores**: Each item inside this node is a store code. Only data for these stores will be read from each attribute for this product. If this node is omitted, 
default scope is used.  
- **simple_data**: Each element inside here is a simple attribute code.  
- **SIMPLE\_ATTRIBUTE\_CODE**: Represents a single simple attribute. The attribute values go to one or more children nodes.  
These children nodes need to match the ones defined in `<stores>` element or they will not be read. For default scope,  
Use the node `<default>`  
- **complex_data**: Complex attributes go here. These are attributes that can have multiple values like categories, media images, related products  
- **enum**: Collection of attributes that belong together.  
- **enum item**: These represent each of the multiple values for a complex attribute. For example, each category or an associated product would be a new item.  
- **enum item ATTRIBUTE_CODE**: Each item is collection of attributes that represent a complex attribute. For example, a media image can be defined by up to 
5 diffrent attributes. It is important that each <item> element has the same children names and count.  


Configuration options
---------------------
Most options should be self-explanatory. 

- Append suffix to duplicates   
By default this is set to yes, which is also the Magento behavior. This means that an image with existing name will be saved with a numbered suffix added to it.
If set to no, images will not be renamed. Additionally, image with the same name will only be copied if it is newer.

- Ignored new attributes  
List of attribute codes that will not be treated as missing.

Compatibility
-------------
- Magento CE >= 1.8.0.0
- Magento EE >= 1.13.1

Current Limitations
-------------------
- When creating missing categories, only one root category is used. In a multi-website shop this would only work for one at a time.  
- If you are using Enterprise scheduled reindexing (version 1.13+), deadlocks can happen if import and indexing run at the same time  

Contribution
------------
Any contribution is appreciated, just open a pull request. If possible stick to the following coding rules:

- Keep your code as simple and as short as possible
- Use speaking method and variable names - this is a very important (or the primary) source of documentation
- Use observers instead of rewrites wherever possible
- Do not duplicate code; so if you copy code from one place to another you are properly doing something wrong
- Only use comments inside methods if the code is really hard to understand and you cannot make it easier; please comment the methods however
- Use sentences for your commit-messages that start with a verb in past tense and end with a dot, e.g. "Add modman file."



License
-------
[Open Software Licence 3.0 (OSL-3.0)](http://opensource.org/licenses/osl-3.0.php)

<?php
//
// Definition of eZDOMNode class
//
// Created on: <16-Nov-2001 12:11:43 bf>
//
// Copyright (C) 1999-2005 eZ systems as. All rights reserved.
//
// This source file is part of the eZ publish (tm) Open Source Content
// Management System.
//
// This file may be distributed and/or modified under the terms of the
// "GNU General Public License" version 2 as published by the Free
// Software Foundation and appearing in the file LICENSE included in
// the packaging of this file.
//
// Licencees holding a valid "eZ publish professional licence" version 2
// may use this file in accordance with the "eZ publish professional licence"
// version 2 Agreement provided with the Software.
//
// This file is provided AS IS with NO WARRANTY OF ANY KIND, INCLUDING
// THE WARRANTY OF DESIGN, MERCHANTABILITY AND FITNESS FOR A PARTICULAR
// PURPOSE.
//
// The "eZ publish professional licence" version 2 is available at
// http://ez.no/ez_publish/licences/professional/ and in the file
// PROFESSIONAL_LICENCE included in the packaging of this file.
// For pricing of this licence please contact us via e-mail to licence@ez.no.
// Further contact information is available at http://ez.no/company/contact/.
//
// The "GNU General Public License" (GPL) is available at
// http://www.gnu.org/copyleft/gpl.html.
//
// Contact licence@ez.no if any conditions of this licencing isn't clear to
// you.
//

/*! \file ezdomnode.php
  DOM node handling
*/

/*!
  \class eZDOMNode ezdomnode.php
  \ingroup eZXML
  \brief eZDOMNode encapsulates XML DOM nodes

  The following node types are supported:
  - Element node, has value \c 1
  - Attribute node, has value \c 2
  - Text node, has value \c 3
  - CDATA node, has value \c 4

  \sa eZXML eZDOMDocument
*/

/*!
 Element node, defines a node which contains attributes and children
*/
define( "EZ_XML_NODE_ELEMENT", 1 );
/*!
 Attribute node, defines a node which contains an attribute name and it's value
*/
define( "EZ_XML_NODE_ATTRIBUTE", 2 );
/*!
 Text node, defines a node which contains a text string encoded by escaping some characters.
*/
define( "EZ_XML_NODE_TEXT", 3 );
/*!
 CDATA node, defines a node which contains a text string encoding in a CDATA structure.
*/
define( "EZ_XML_NODE_CDATASECTION", 4 );

class eZDOMNode
{
    /*!
      Initializes the DOM node.
    */
    function eZDOMNode()
    {
        $this->content =& $this->value;
        $this->Content =& $this->content;
        $this->Type =& $this->type;
    }

    /*!
     Makes a copy of the current node and returns a reference to it.
    */
    function clone_node()
    {
        $tmp = new eZDOMNode();
        $tmp->Name = $this->Name;
        $tmp->Type = $this->Type;
        $tmp->Content = $this->Content;
        $tmp->Children = $this->Children;
        $tmp->Attributes = $this->Attributes;
        $tmp->NamespaceURI = $this->NamespaceURI;
        $tmp->LocalName = $this->LocalName;
        $tmp->Prefix = $this->Prefix;
        return $tmp;
    }

    /*!
      \return The name of the node.

      For element and attributes nodes this will the name supplied when creating the node,
      for text nodes it returns \c #text and CDATA returns \c #cdata-section
    */
    function name()
    {
        return $this->Name;
    }

    /*!
      Sets the current name to \a $name.
    */
    function setName( $name )
    {
        $this->Name = $name;
        $this->LocalName = $name;
    }

    /*!
      \return The namespace URI for the node or \c false if no URI
    */
    function namespaceURI()
    {
        return $this->NamespaceURI;
    }

    /*!
      Sets the namespace URI of the node to \a $uri.
    */
    function setNamespaceURI( $uri )
    {
        $this->NamespaceURI = $uri;
    }

    /*!
      Returns the local name of the node if the node uses namespaces. If not false is returned.
    */
    function localName()
    {
        return $this->LocalName;
    }

    /*!
      \return The prefix of the nodes name, this will be the namespace for the node.
    */
    function prefix()
    {
        return $this->Prefix;
    }

    /*!
      Sets the namespace prefix for this node to \a $value.
    */
    function setPrefix( $value )
    {
        $this->Prefix = $value;
    }

    /*!
      \return An integer value which describes the type of node.

      The type is one of:
      - 1 - Element node, that is a node which contains attributes and children.
      - 2 - Attribute node, this is a node which contains a name and a value.
      - 3 - Text node, this is a node which contains a text string
      - 4 - CDATA node, this is a node which contains a text string
    */
    function type()
    {
        return $this->Type;
    }

    /*!
      Sets the node type to \a $type.

      Use one of the following defines for the type:
      - EZ_XML_NODE_ELEMENT - Element nodes
      - EZ_XML_NODE_ATTRIBUTE - Attribute nodes
      - EZ_XML_NODE_TEXT - Text nodes
      - EZ_XML_NODE_CDATASECTION - CDATA nodes
    */
    function setType( $type )
    {
        $this->Type = $type;
    }

    /*!
      \return The content of the node or \c false if it does not contain any content.

      \note This will only make sense for text and CDATA nodes.
    */
    function &content()
    {
        return $this->Content;
    }

    /*!
      Sets the content of the node to the \a $content.

      \note This will only make sense for text and CDATA nodes.
    */
    function setContent( $content )
    {
        $this->Content = $content;
    }

    /*!
      \return An array with attribute nodes.

      \note This will only make sense for element nodes.
    */
    function &attributes()
    {
        return $this->Attributes;
    }

    /*!
      \return An array with attribute nodes matching the namespace URI \a $namespaceURI.

      \note This will only make sense for element nodes.
    */
    function &attributesNS( $namespaceURI )
    {
        $ret = array();
        if ( count( $this->Attributes  ) > 0 )
        {
            foreach ( $this->Attributes as $attribute )
            {
                if ( $attribute->namespaceURI() == $namespaceURI )
                {

                    $ret[] = $attribute;
                }
            }
        }
        return $ret;
    }

    /*!
      \return \c true if the node has any attributes.

      \note This will only make sense for element nodes.
    */
    function hasAttributes()
    {
        return count( $this->Attributes ) > 0;
    }

    /*!
      \return The number of attributes for the node.

      \note This will only make sense for element nodes.
    */
    function attributeCount()
    {
        return count( $this->Attributes );
    }

    /*!
      \return An array with child nodes.

      \note This will only make sense for element nodes.
    */
    function children()
    {
        return $this->Children;
    }

    /*!
      \return \c true if the node has children.

      \note This will only make sense for element nodes.
    */
    function hasChildren()
    {
        return count( $this->Children ) > 0;
    }

    /*!
      \return The number of children for the node.

      \note This will only make sense for element nodes.
    */
    function childrenCount()
    {
        return count( $this->Children );
    }

    /*!
      \return The first child of the node or \c null if there are no children.

      \note This will only make sense for element nodes.
    */
    function &firstChild()
    {
        if ( count( $this->Children ) == 0 )
        {
            $child = false;
            return $child;
        }
        return $this->Children[0];
    }

    /*!
      Finds the first element named \a $name and returns the children of that node.
      If no element node is found it returns \c false.

      \note This will only make sense for element nodes.
      \note If multiple elements with that name is found \c false is returned.
      \sa elementByName, children
    */
    function elementChildrenByName( $name )
    {
        $element =& $this->elementByName( $name );
        if ( !$element )
        {
            $children = false;
            return $children;
        }
        return $element->children();
    }

    /*!
      Finds the first element named \a $name and returns the first child of that node.
      If no element node is found or there are not children it returns \c false.

      \note This will only make sense for element nodes.
      \note If multiple elements with that name is found \c false is returned.
      \sa elementByName, firstChild
    */
    function &elementFirstChildByName( $name )
    {
        $element =& $this->elementByName( $name );
        if ( !$element )
        {
            $child = false;
            return $child;
        }
        return $element->firstChild();
    }

    /*!
      \returns The first element that is named \a $name.
               If multiple elements with that name is found \c false is returned.

      \note This will only make sense for element nodes.
      \sa elementsByName
    */
    function &elementByName( $name )
    {
        $element = false;
        foreach ( array_keys( $this->Children ) as $key )
        {
            $child =& $this->Children[$key];
            if ( $child->name() == $name )
            {
                if ( $element )
                {
                    $retValue = false;
                    return $retValue;
                }
                $element =& $child;
            }
        }
        return $element;
    }

    /*!
     Alias for libxml compatibility
    */
    function get_elements_by_tagname( $name )
    {
        return $this->elementsByName( $name );
    }

    /*!
      Finds the first element named \a $name and returns the text content of that node.
      If no element node is found or no text content exists it returns \c false.

      \note This will only make sense for element nodes.
      \note If multiple elements with that name is found \c false is returned.
      \sa elementByName, textContent
    */
    function elementTextContentByName( $name )
    {
        $element = $this->elementByName( $name );
        if ( !$element )
        {
            return false;
        }

        return $element->textContent();
    }

    /*!
     \param attribute name
     \param attribute value

     \return element by attribute value
    */
    function &elementByAttributeValue( $attr, $value )
    {
        foreach ( array_keys( $this->Children ) as $key )
        {
            $child =& $this->Children[$key];
            if ( $child->attributeValue( $attr ) == $value )
            {
                return $child;
            }
        }
        $child = false;
        return $child;
    }

    /*!
      \return An array with elements that matches the name \a $name.

      \note This will only make sense for element nodes.
      \sa elementByName
    */
    function elementsByName( $name )
    {
        $elements = array();
        foreach ( array_keys( $this->Children ) as $key )
        {
            $child =& $this->Children[$key];
            if ( $child->name() == $name )
            {
                $elements[] =& $child;
            }
        }
        return $elements;
    }

    /*!
      \return An array with text contents taken from all child nodes which matches the name \a $name.

      \note This will only make sense for element nodes.
      \sa elementsByName, textContent
    */
    function &elementsTextContentByName( $name )
    {
        $elements = array();
        foreach ( array_keys( $this->Children ) as $key )
        {
            $child =& $this->Children[$key];
            if ( $child->name() == $name )
            {
                $elements[] = $child->textContent();
            }
        }
        return $elements;
    }

    /*!
      \return The value of the attribute named \a $attributeName.
      If no value is found \c false is returned.

      \note This will only make sense for element nodes.
    */
    function attributeValue( $attributeName )
    {
        $returnValue = false;
        foreach ( $this->Attributes as $attribute )
        {
            if ( $attribute->name() == $attributeName )
                $returnValue = $attribute->content();
        }

        return $returnValue;
    }

    /*!
      Alias for libxml compatibility
    */
    function get_attribute( $attributeName )
    {
        return $this->attributeValue( $attributeName );
    }

    /*!
      Finds the first element named \a $name and returns the value of the attribute named \a $attributeName.
      If no element node is found or no attribute with the given name exists it returns \c false.

      \note This will only make sense for element nodes.
      \note If multiple elements with that name is found \c false is returned.
      \sa elementByName, attributeValue
    */
    function elementAttributeValueByName( $name, $attributeName )
    {
        $element = $this->elementByName( $name );
        if ( !$element )
            return false;
        else
            return $element->attributeValue( $attributeName );
    }

    /*!
      Goes trough all attributes of the node and matches the attribute names
      with the parameter \a $attributeDefinitions.

      \param $attributeDefinitions An associative array which maps from matching attribute name to lookup name.
      \param $defaultValue If other value than \c null it will be set as value for all lookup names that didn't match

      The matching attribute name in the will be matched against the attributes of the node.
      When a match is found the attribute value will be fetched and placed in the returned
      associative array using lookup name as key.

      A code example will explain this, the variable \a $songNode contains the following xml
      \code
      <song name="Shine On You Crazy Diamond" track="1" />
      \endcode

      The PHP code is.
      \code
      $def = array( 'name' => 'song_name',
                    'track' => 'track_number' );
      $values = $songNode->attributeValues( $def );
      \encode

      \a $values will now contain.
      \code
      array( 'song_name' => 'Shine On You Crazy Diamond',
             'track_number => '1' )
      \endcode

      This method and appendAttributes() work together, the values inserted with appendAttributes()
      can be extracted with this method.

      \note This will only make sense for element nodes.
      \sa elementAttributeValueByName, appendAttributes
    */
    function attributeValues( $attributeDefinitions, $defaultValue = null )
    {
        $hash = array();
        foreach ( $this->Attributes as $attribute )
        {
            foreach ( $attributeDefinitions as $attributeName => $keyName )
            {
                if ( $attribute->name() == $attributeName )
                {
                    $hash[$keyName] = $attribute->content();
                    break;
                }
            }
        }
        if ( $defaultValue !== null )
        {
            foreach ( $attributeDefinitions as $attributeName => $keyName )
            {
                if ( !isset( $hash[$keyName] ) )
                    $hash[$keyName] = $defaultValue;
            }
        }

        return $hash;
    }

    /*!
      \return The value of the attribute named \a $attributeName and having namespace \a $namespaceURI.
      If no value is found \c false is returned.

      \note This will only make sense for element nodes.
    */
    function attributeValueNS( $attributeName, $namespaceURI )
    {
        $returnValue = false;
        if ( count( $this->Attributes  ) > 0 )
        {
            foreach ( $this->Attributes as $attribute )
            {
                if ( $attribute->name() == $attributeName &&
                     $attribute->namespaceURI() == $namespaceURI )
                {

                    $returnValue = $attribute->content();
                }
            }
        }

        return $returnValue;
    }

    /*!
      Appends the node \a $node as a child of the current node.

      \return The node that was just inserted or \c false if it failed to insert a node.

      \note This will only make sense for element nodes.
    */
    function appendChild( &$node )
    {
        if ( get_class( $node ) == "ezdomnode" )
        {
            $this->Children[] =& $node;
            return $node;
        }
        return false;
    }

    /*!
     Alias for libXML compatibility
    */
    function append_child( &$node )
    {
        return $this->appendChild( $node );
    }

    /*!
      Appends the attribute node \a $node as an attribute of the current node.

      \return The attribute node that was just inserted or \c false if it failed to insert an attribute.

      \note This will only make sense for element nodes.
    */
    function appendAttribute( &$node )
    {
        if ( get_class( $node ) == "ezdomnode" )
        {
            $this->Attributes[] =& $node;
            return $node;
        }
        return false;
    }

    function set_attribute( $name, $value )
    {
        $this->removeNamedAttribute( $name );
        return $this->appendAttribute( eZDOMDocument::createAttributeNode( $name, $value ) );
    }

    /*!
      Appends multiple attributes and attribute values.

      \param $attributeValues An associative array containing the attribute values to insert,
                              it maps from lookup name to attribute value.
      \param $attributeDefinitions An associative array defining how lookup names maps to attribute names,
                                   the array key is the attribute name and the array value the lookup name.
      \param $includeEmptyValues If \c true it will set attribute values even though they don't exist in \a $attributeValues

      \code
      $definition = array( 'name' => 'song_name',
                           'track' => 'track_name' );
      $values = array( 'song_name' => 'Shine On You Crazy Diamond',
                       'track_number' => '1' );
      $node->appendAttributes( $values, $definition );
      \encode

      The node will then look like.
      \code
      <song name="Shine On You Crazy Diamond" track="1" />
      \endcode

      This method and attributeValues() work together, the returned result of attributeValues()
      can be inserted with this method.

      \note This will only make sense for element nodes.
      \sa attributeValues
    */
    function appendAttributes( $attributeValues,
                               $attributeDefinitions,
                               $includeEmptyValues = false )
    {
        foreach ( $attributeDefinitions as $attributeXMLName => $attributeKey )
        {
            if ( $includeEmptyValues or
                 ( isset( $attributeValues[$attributeKey] ) and
                   $attributeValues[$attributeKey] !== false ) )
            {
                $value = false;
                if ( isset( $attributeValues[$attributeKey] ) and
                     $attributeValues[$attributeKey] !== false )
                    $value = $attributeValues[$attributeKey];
                $this->Attributes[] = eZDOMDocument::createAttributeNode( $attributeXMLName, $value );
            }
        }
    }

    /*!
      Removes the attribute node named \a $name.
      \return The removed attribute node or \c false if no such node exists.

      \note This will only make sense for element nodes.
    */
    function removeNamedAttribute( $name )
    {
        $removed = false;
        foreach( array_keys( $this->Attributes ) as $key )
        {
            if ( $this->Attributes[$key]->name() == $name )
            {
                unset( $this->Attributes[$key] );
                $removed = true;
            }
        }
        return $removed;
    }

    /*!
     Alias for libxml compatibility
    */
    function remove_attribute( $name )
    {
        return $this->removeNamedAttribute( $name );
    }

    /*!
      Removes all attribute from the node.

      \note This will only make sense for element nodes.
    */
    function removeAttributes()
    {
        $this->Attributes = array();
    }

    /*!
      Removes all child nodes that matches the name \a $name.
      \return \c true if it removed any nodes, otherwise \c false.

      \note This will only make sense for element nodes.
    */
    function removeNamedChildren( $name )
    {
        $removed = false;
        foreach( array_keys( $this->Children ) as $key )
        {
            if ( $this->Children[$key]->name() == $name )
            {
                unset( $this->Children[$key] );
                $removed = true;
            }
        }
        return $removed;
    }

    /*!
      Removes all child nodes from the current node.

      \note This will only make sense for element nodes.
    */
    function removeChildren()
    {
        $this->Children = array();
    }

    /*!
      Removes the last child node of the current node.

      \note This will only make sense for element nodes.
    */
    function removeLastChild( )
    {
        end( $this->Children );
        $key = key( $this->Children );
        unset( $this->Children[$key] );
    }

    /*!
      Removes child by the given child object.
    */
    function removeChild( &$childToRemove )
    {
        foreach ( array_keys( $this->Children ) as $key )
        {
            if ( $childToRemove == $this->Children[$key] )
            {
                unset( $this->Children[$key] );
            }
        }
    }

    /*!
     \return The last child node or \c false if there are no children.

      \note This will only make sense for element nodes.
    */
    function lastChild()
    {
        if ( is_array( $this->Children ) )
        {
            return end( $this->Children );
        }

        return false;
    }

    /*!
      \return The content() of the first child node or \c false if there are no children.

      \note This will only make sense for element nodes.
      \sa elementTextContentByName
    */
    function textContent( )
    {
        $children = $this->children();

        if ( count( $children ) == 1 )
            return $children[0]->content();
        else
            return false;
    }

    /*!
      \return A string that represents the current node.
      The string will be created according to the node type which are:
      - Element node, places the name in <>, expands all attributes and calls toString() on all children.
      - Text node, returns the content() by escaping the characters & < > ' and ".
      - CDATA node, returns the text wrapped in <![CDATA[ and ]]

      \param $level The current tab level, starts at 0 and is increased by 1 for each recursion
      \param $charset Which charset the text will be encoded in, currently not used

      Example strings.
      \code
      '<song name="Shine On You Crazy Diamond" track="1" />'
      'This &amp; that &quot;wrapped&quot; in &lt;div&gt; tags'
      '<![CDATA[This & that "wrapped" in <div> tags'
      \endcode

      \note This will only make sense for element nodes.
    */
    function toString( $level, $charset = false )
    {
        $spacer = str_repeat( " ", $level*2 );
        $ret = "";
        switch ( $this->Name )
        {
            case "#text" :
            {
                $tagContent = $this->Content;

                $tagContent = str_replace( "&", "&amp;", $tagContent );
                $tagContent = str_replace( ">", "&gt;", $tagContent );
                $tagContent = str_replace( "<", "&lt;", $tagContent );
                $tagContent = str_replace( "'", "&apos;", $tagContent );
                $tagContent = str_replace( '"', "&quot;", $tagContent );

                $ret = $tagContent;
            }break;

            case "#cdata-section" :
            {
                $ret = "<![CDATA[";
                $ret .= $this->Content;
                $ret .= "]]>";
            }break;

            default :
            {
                $isOneLiner = false;
                // check if it's a oneliner
                if ( count( $this->Children ) == 0 and ( $this->Content == "" ) )
                    $isOneLiner = true;

                $attrStr = "";

                // check for namespace definition
                if ( $this->namespaceURI() != "" )
                {
                    $attrPrefix = "";
                    if ( $this->Prefix != "" )
                        $attrPrefix = ":" . $this->prefix();
                    $attrStr = " xmlns" . $attrPrefix . "=\"" . $this->namespaceURI() . "\"";
                }

                $prefix = "";
                if ( $this->Prefix != false )
                    $prefix = $this->Prefix. ":";

                // generate attributes string
                if ( count( $this->Attributes ) > 0 )
                {
                    $i = 0;
                    foreach ( $this->Attributes as $attr )
                    {
                        $attrPrefix = "";
                        if ( $attr->prefix() != false )
                            $attrPrefix = $attr->prefix(). ":";

                        if ( $i > 0 )
                            $attrStr .= "\n" . $spacer . str_repeat( " ", strlen( $prefix . $this->Name ) + 1 + 1  );
                        else
                            $attrStr .= ' ';

                        $attrContent = $attr->content();
                        $attrContent = str_replace( "&", "&amp;", $attrContent );
                        $attrContent = str_replace( ">", "&gt;", $attrContent );
                        $attrContent = str_replace( "<", "&lt;", $attrContent );
                        $attrContent = str_replace( "'", "&apos;", $attrContent );
                        $attrContent = str_replace( '"', "&quot;", $attrContent );

                        $attrStr .=  $attrPrefix . $attr->name() . "=\"" . $attrContent . "\"";
                        ++$i;
                    }
                }

                if ( $isOneLiner )
                    $oneLinerEnd = " /";
                else
                    $oneLinerEnd = "";

                $ret = '';
                if ( $level > 0 )
                    $ret .= "\n";
                $ret .= "$spacer<" . $prefix . $this->Name . $attrStr . $oneLinerEnd . ">";

                $lastChildType = false;
                if ( count( $this->Children ) > 0 )
                {
                    foreach ( $this->Children as $child )
                    {
                        $ret .= $child->toString( $level + 1 );
                        $lastChildType = $child->type();
                    }
                }

                if ( !$isOneLiner )
                {
                    if ( $lastChildType == 1 )
                        $ret .= "\n$spacer";
                    $ret .= "</" . $prefix . $this->Name . ">";
                }
//                    $ret .= "$spacer</" . $prefix . $this->Name . ">\n";

            }break;
        }
        return $ret;
    }

    /*!
     Alias for libxml compatibility
    */
    function dump_mem( $format, $charset = false )
    {
        return $this->toString( 0, $charset);
    }

    /// \privatesection

    /// Name of the node
    var $Name = false;

    /// Type of the DOM node. ElementNode=1, AttributeNode=2, TextNode=3, CDATASectionNode=4
    var $type;
    var $Type = EZ_XML_NODE_ELEMENT;

    /// Content of the node
    var $content = "";
    var $Content = "";
    var $value = '';

    /// Subnodes
    var $Children = array();

    /// Attributes
    var $Attributes = array();

    /// Contains the namespace URI. E.g. xmlns="http://ez.no/article/", http://ez.no/article/ would be the namespace URI
    var $NamespaceURI = false;

    /// The local part of a name. E.g: book:title, title is the local part
    var $LocalName = false;

    /// contains the namespace prefix. E.g: book:title, book is the prefix
    var $Prefix = false;
}

?>

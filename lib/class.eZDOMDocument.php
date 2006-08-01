<?php
//
// Definition of eZDOMDocument class
//
// Created on: <16-Nov-2001 12:18:23 bf>
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

/*!
  \class eZDOMDocument ezdomdocument.php
  \ingroup eZXML
  \brief eZDOMDocument handles DOM nodes in DOM documents

  The DOM document keeps a tree of DOM nodes and maintains
  information on the various namespaces in the tree.
  It also has helper functions for creating nodes and serializing
  the tree into text.

  Accessing and changing the name of the document is done using name() and setName().

  Accessing the tree is done using the root() method, use setRoot() to set
  a new root node. The method appendChild() will do the same as setRoot().

  For fetching nodes globally the methods elementsByName(), elementsByNameNS() and namespaceByAlias()
  can be used. They will fetch nodes according to the fetch criteria no matter where they are in the tree.

  Creating new nodes is most easily done with the helper methods
  createTextNode(), createCDATANode(), createElementNode() and createAttributeNode().
  They take care of creating the node with the correct type and does proper initialization.

  Creating typical structures is also possible with the helper methods
  createElementTextNode(), createElementCDATANode(), createElementNodeNS(),
  createAttributeNamespaceDefNode() and createAttributeNodeNS().
  This will not only create the node itself but also the correct subchild, attribute or namespace.

  After nodes are created they can be registered with the methods registerElement(), registerNamespaceAlias().
  This ensures that they are available globally in the DOM document.

  Serializing the tree structure is done using toString(), this also allows specifying
  the character set of the output.

  Example of using the DOM document to create a node structure.
  \code
  $doc = new eZDOMDocument();
  $doc->setName( "FishCatalogue" );

  $root = $doc->createElementNode( "FishCatalogue" );
  $doc->setRoot( $root );

  $freshWater = $doc->createElementNode( "FreshWater" );
  $root->appendChild( $freshWater );

  $saltWater = $doc->createElementNode( "SaltWater" );
  $root->appendChild( $saltWater );

  $guppy = $doc->createElementNode( "Guppy" );
  $guppy->appendChild( $doc->createTextNode( "Guppy is a small livebreeder." ) );

  $freshWater->appendChild( $guppy );

  $cod = $doc->createElementNode( "Cod" );
  $saltWater->appendChild( $cod );

  $cod->appendChild( $doc->createCDATANode( "A big dull fish <-> !!" ) );

  print( $doc->toString() );

  // will print the following
    <?xml version="1.0"?>
    <FishCatalogue>
      <FreshWater>
        <Guppy>
    Guppy is a small livebreeder.    </Guppy>
      </FreshWater>
      <SaltWater>
        <Cod>
    <![CDATA[A big dull fish <-> !!]]>    </Cod>
      </SaltWater>
    </FishCatalogue>

  \endcode
*/

LoadObjectDependency('_FreeMED.eZDOMNode');

class eZDOMDocument
{
    /*!
      Initializes the DOM document object with the name \a $name.
    */
    function eZDOMDocument( $name = "" )
    {
        $this->Name = $name;
    }

    /*!
      Sets the document name to \a $name.
    */
    function setName( $name )
    {
        $this->Name = $name;
    }

    /*!
      \return The document root node if it exists, if not \c false is returned.
    */
    function &root()
    {
        return $this->Root;
    }

    /*!
      \return The document root node if it exists, if not \c false is returned.
      Extra method for libxml compatibility.
    */
    function &get_root()
    {
        return $this->Root;
    }

    /*!
      Sets the document root node to \a $node.
      If the parameter is not an eZDOMNode it will not be set.
    */
    function setRoot( &$node )
    {
        if ( get_class( $node ) == "ezdomnode" )
        {
            $this->Root =& $node;
        }
    }

    /*!
      Sets the document root node to \a $node.
      If the parameter is not an eZDOMNode it will not be set.
      \sa setRoot()
    */
    function appendChild( &$node )
    {
        if ( get_class( $node ) == "ezdomnode" )
        {
            $this->Root =& $node;
        }
    }

    /*!
      Finds all element nodes which matches the name \a $name and returns it.
      \return An array with eZDOMNode elements.
    */
    function &elementsByName( $name )
    {
        return $this->NamedNodes[$name];
    }

    /*!
     Alias for libxml compatibility
    */
    function get_elements_by_tagname( $name )
    {
        return $this->elementsByName( $name );
    }

    /*!
      Finds all element nodes which matches the name \a $name and namespace URI \a $namespaceURI and returns it.
      \return An array with eZDOMNode elements.
    */
    function &elementsByNameNS( $name, $namespaceURI )
    {
        return $this->NamedNodesNS[$name][$namespaceURI];
    }

    /*!
      Finds all element nodes which matches the namespace alias \a $alias and returns it.
      \return An array with eZDOMNode elements.
    */
    function namespaceByAlias( $alias )
    {
        if ( isset( $this->Namespaces[$alias] ) )
        {
            return $this->Namespaces[$alias];
        }
        else
        {
            return false;
        }
    }

    /*!
      \static
      Creates a DOM node of type text and returns it.

      Text nodes are used to store text strings,
      use content() on the node to extract the text.

      \param $text The text string which will be stored in the node

      \code
      $dom->createTextNode( 'Edge' );
      \endcode
      The resulting XML text will be
      \code
      Edge
      \endcode
    */
    function createTextNode( $text )
    {
        /* We remove all control chars from the text, although they
         * should have not be there in the first place. This is
         * iso-8859-1 and UTF-8 safe. Those characters might also never exist
         * in an XML document in the first place
         * (http://w3.org/TR/2004/REC-xml-20040204/#NT-Char) so it's safe to
         * remove them */
        $text = preg_replace('/[\x00-\x08\x0b-\x0c\x0e-\x1f]/', '', $text);

        $node = new eZDOMNode();
        $node->setName( "#text" );
        $node->setContent( $text );
        $node->setType( 3 );

        return $node;
    }

    /*!
      \static
      Creates a DOM node of type CDATA and returns it.

      CDATA nodes are used to store text strings,
      use content() on the node to extract the text.

      \param $text The text string which will be stored in the node

      \code
      $dom->createCDATANode( 'http://ez.no' );
      \endcode
      The resulting XML text will be
      \code
      <![CDATA[http://ez.no]]>
      \endcode
    */
    function createCDATANode( $text )
    {
        $node = new eZDOMNode();
        $node->setName( "#cdata-section" );
        $node->setContent( $text );
        $node->setType( 4 );

        return $node;
    }

    /*!
      \static
      Creates DOMNodeElement recursivly from recursive array
    */
    function createElementNodeFromArray( $name, $array )
    {
        $node = new eZDOMNode();
        $node->setName( $name );
        $node->setType( 1 );

        foreach ( $array as $arrayKey => $value )
        {
            if ( is_array( $value ) and
                 count( $valueKeys = array_keys( $value ) ) > 0 )
            {
                if ( is_int( $valueKeys[0] ) )
                {
                    foreach( $value as $child )
                    {
                        $node->appendChild( eZDOMDocument::createElementNodeFromArray( $arrayKey, $child ) );
                    }
                }
                else
                {
                    $node->appendChild( eZDOMDocument::createElementNodeFromArray( $arrayKey, $value ) );
                }
            }
            else
            {
                $node->appendAttribute( eZDomDocument::createAttributeNode( $arrayKey, $value ) );
            }
        }

        return $node;
    }

    /*!
      \static
      Creates recursive array from DOMNodeElement
    */
    function createArrayFromDOMNode( $domNode )
    {
        if ( !$domNode )
        {
            return null;
        }

        $retArray = array();
        foreach ( $domNode->children() as $childNode )
        {
            if ( !isset( $retArray[$childNode->name()] ) )
            {
                $retArray[$childNode->name()] = array();
            }

            // If the node has children we create an array for this element
            // and append to it, if not we assign it directly
            if ( $childNode->hasChildren() )
            {
                $retArray[$childNode->name()][] = eZDOMDocument::createArrayFromDOMNode( $childNode );
            }
            else
            {
                $retArray[$childNode->name()] = eZDOMDocument::createArrayFromDOMNode( $childNode );
            }
        }
        foreach( $domNode->attributes() as $attributeNode )
        {
            $retArray[$attributeNode->name()] = $attributeNode->content();
        }

        return $retArray;
    }

    /*!
      \static
      Creates a DOM node of type element and returns it.

      Element nodes are the basic node type in DOM tree,
      they are used to structure nodes.
      They can contain child nodes and attribute nodes accessible
      with children() and attributes().

      \param $name The name of the element node.
      \param $attributes An associative array with attribute names and attribute data.
                         This can be used to quickly fill in node attributes.

      \code
      $dom->createElementNode( 'song',
                               array( 'name' => 'Shine On You Crazy Diamond',
                                      'track' => 1 ) );
      \endcode
      The resulting XML text will be
      \code
      <song name='Shine On You Crazy Diamond' track='1' />
      \endcode
    */
    function createElementNode( $name, $attributes = array() )
    {
        $node = new eZDOMNode();
        $node->setName( $name );
        $node->setType( 1 );
        foreach ( $attributes as $attributeKey => $attributeValue )
        {
            $node->appendAttribute( eZDomDocument::createAttributeNode( $attributeKey, $attributeValue ) );
        }

        return $node;
    }

    /*!
     Alias for libXML compatibility
    */
    function create_element( $name, $attributes = array() )
    {
        return $this->createElementNode( $name, $attributes );
    }

    /*!
      \static
      Creates a DOM node of type element and returns it.
      It will also create a DOM node of type text and add it as child of the element node.

      \param $name The name of the element node.
      \param $text The text string which will be stored in the text node
      \param $attributes An associative array with attribute names and attribute data.
                         This can be used to quickly fill in element node attributes.

      \code
      $dom->createElementTextNode( 'name',
                                   'Archer Maclean',
                                    array( 'id' => 'archer',
                                           'game' => 'ik+' ) );
      \endcode
      The resulting XML text will be
      \code
      <name id='archer' game='ik+'>Archer Maclean</name>
      \endcode

      \sa createTextNode, createElementNode
    */
    function createElementTextNode( $name, $text, $attributes = array() )
    {
        $node = eZDOMDocument::createElementNode( $name, $attributes );
        $textNode = eZDOMDocument::createTextNode( $text );
        $node->appendChild( $textNode );

        return $node;
    }

    /*!
      \static
      Creates a DOM node of type element and returns it.
      It will also create a DOM node of type CDATA and add it as child of the element node.

      \param $name The name of the element node.
      \param $text The text string which will be stored in the CDATA node
      \param $attributes An associative array with attribute names and attribute data.
                         This can be used to quickly fill in element node attributes.

      \code
      $dom->createElementCDATANode( 'name',
                                    'Peter Molyneux',
                                     array( 'type' => 'developer',
                                            'game' => 'dungeon keeper' ) );
      \endcode
      The resulting XML text will be
      \code
      <name type='developer' game='dungeon keeper'><![CDATA[Peter Molyneux]]></name>
      \endcode

      \sa createCDATANode, createElementNode
    */
    function createElementCDATANode( $name, $text, $attributes = array() )
    {
        $node = eZDOMDocument::createElementNode( $name, $attributes );
        $cdataNode = eZDOMDocument::createCDATANode( $text );
        $node->appendChild( $cdataNode );

        return $node;
    }

    /*!
      \static
      Creates a DOM node of type element with a namespace and returns it.

      \param $uri The namespace URI for the element
      \param $name The name of the element node.

      \code
      $dom->createElementNodeNS( 'http://ez.no/package',
                                 'package' );
      \endcode
      The resulting XML text will be
      \code
      <package xmlns="http://ez.no/package" />
      \endcode

      \sa createElementNode
    */
    function createElementNodeNS( $uri, $name )
    {
        $node = new eZDOMNode();
        $node->setNamespaceURI( $uri );
        $node->setName( $name );
        $node->setType( 1 );

        return $node;
    }

    /*!
      \static
      Creates a DOM node of type attribute and returns it.

      \param $name The name of the attribute
      \param $content The content of the attribute
      \param $prefix Namespace prefix which will be placed before the attribute name

      \code
      $dom->createAttributeNode( 'name',
                                 'Pink Floyd',
                                 'music-group' );
      \endcode
      The resulting XML text will be
      \code
      music-group:name="Pink Floyd"
      \endcode
    */
    function createAttributeNode( $name, $content, $prefix = false )
    {
        $node = new eZDOMNode();
        $node->setName( $name );
        if ( $prefix )
            $node->setPrefix( $prefix );
        $node->setContent( $content );
        $node->setType( 2 );

        return $node;
    }

    /*!
      \static
      Creates a DOM node of type attribute which is used for namespace definitions and returns it.

      \param $prefix Namespace prefix which will be placed before the attribute name
      \param $uri The unique URI for the namespace

      \code
      $dom->createAttributeNamespaceDefNode( 'music-group',
                                             'http://music.org/groups' );
      \endcode
      The resulting XML text will be
      \code
      xmlns:music-group="http://music.org/groups"
      \endcode
    */
    function createAttributeNamespaceDefNode( $prefix, $uri )
    {
        $node = new eZDOMNode();
        $node->setName( $prefix );
        $node->setPrefix( "xmlns" );
        $node->setContent( $uri );
        $node->setType( 2 );

        return $node;
    }

    /*!
      \static
      Creates a DOM node of type attribute which is used for namespace definitions and returns it.

      \param $uri The unique URI for the namespace
      \param $name The name of the attribute
      \param $content The content of the attribute

      \code
      $dom->createAttributeNodeNS( 'http://music.org/groups',
                                   'name',
                                   'Pink Floyd' );
      \endcode
      The resulting XML text will be
      \code
      name="Pink Floyd"
      \endcode
    */
    function createAttributeNodeNS( $uri, $name, $content )
    {
        $node = new eZDOMNode();
        $node->setName( $name );
//        $node->setPrefix( $prefix );
        $node->setNamespaceURI( $uri );
        $node->setContent( $content );
        $node->setType( 2 );

        return $node;
    }

    /*!
      Returns a XML string representation of the DOM document.

      \param $charset The name of the output charset or \c false to use UTF-8 (default in XML)
      \param $charsetConversion Controls whether the resulting text is converted to the specified
                                charset or not.

      The \a $charsetConversion parameter can be useful when you know the inserted texts are
      in the correct charset, turning conversion off can speed things up.

      The XML creation is done by calling the eZDOMNode::toString() function on the root node
      and let that handle the rest.

      \note The charset conversion is smart enough to only do conversion when required
      \note Using charset conversion will require the ezi18n library being installed
    */
    function toString( $charset = true, $charsetConversion = true )
    {
        $charsetText = '';
        if ( $charset === true )
            $charset = 'UTF-8';
        if ( $charset !== false )
            $charsetText = " encoding=\"$charset\"";
        $text = "<?xml version=\"1.0\"$charsetText?>\n";

        if ( get_class( $this->Root ) == "ezdomnode" )
        {
            $text .= $this->Root->toString( 0, $charset );
        }

        return $text;
    }

    /*!
     Alias for libxml compatibility
    */
    function dump_mem( $charset = true, $conversion = true )
    {
        return $this->toString( $charset, $conversion );
    }

    /*!
      Registers the node element \a $node in the DOM document.
      This involves extracting the name of the node and add it to the
      name lookup table which elementsByName() uses, then adding it
      to the namespace lookup table which elementsByNameNS() uses.

      \note This will not insert the node into the node tree.
    */
    function registerElement( &$node )
    {
        $this->NamedNodes[$node->name()][] =& $node;

        if ( $node->namespaceURI() != "" )
        {
            $this->NamedNodesNS[$node->name()][$node->namespaceURI()][] =& $node;
        }
    }

    /*!
     Register the namespace alias \a $alias to point to the namespace \a $namespace.

     The namespace can then later on be fetched with namespaceByAlias().
    */
    function registerNamespaceAlias( $alias, $namespace )
    {
        $this->Namespaces[$alias] =& $namespace;
    }

    /// \privatesection

    /// Document name
    var $Name;

    /// XML version
    var $Version;

    /// Contains an array of reference to the named nodes
    var $NamedNodes = array();

    /// Contains an array of references to the named nodes with namespace
    var $NamedNodesNS = array();

    /// Contains an array of the registered namespaces and their aliases
    var $Namespaces = array();

    /// Reference to the first child of the DOM document
    var $Root;
}

?>

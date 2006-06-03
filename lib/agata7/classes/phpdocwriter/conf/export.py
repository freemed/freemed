# 
# 	This file is part of PHP DocWriter (http://ciclope.info/~jmsanchez)
# 	Copyright (c) 2003-2004 José Manuel Sánchez Rivero
# 
# 	You can contact the author of this software via E-mail at
# 	jmsanchez@laurel.datsi.fi.upm.es
# 
# 	PHP DocWriter is free software; you can redistribute it and/or modify
# 	it under the terms of the GNU Lesser General Public License as published by
# 	the Free Software Foundation; either version 2.1 of the License, or
# 	(at your option) any later version.
# 
# 	PHP DocWriter is distributed in the hope that it will be useful,
# 	but WITHOUT ANY WARRANTY; without even the implied warranty of
# 	MERCHANTABILITY or FITNESS FOR A PARTICULAR PURPOSE.  See the
# 	GNU Lesser General Public License for more details.
# 
# 	You should have received a copy of the GNU Lesser General Public License
# 	along with PHP DocWriter; if not, write to the Free Software
# 	Foundation, Inc., 59 Temple Place, Suite 330, Boston, MA  02111-1307  USA
# 
import getopt,sys
import uno
from unohelper import Base,systemPathToFileUrl, absolutize
from os import getcwd

from com.sun.star.beans import PropertyValue
from com.sun.star.beans.PropertyState import DIRECT_VALUE
from com.sun.star.uno import Exception as UnoException
from com.sun.star.io import IOException

retVal = 0
doc = None

try:
	opts, args = getopt.getopt(sys.argv[1:], "c:",["connection-string=","PDF","MSXP","MS95","MS60","RTF","HTML","SW5","SW4","SW3","TXT","TXTE","TEX","XHTML10","XHTML11"])
	url = "uno:socket,host=localhost,port=2002;urp;StarOffice.ComponentContext"
	for o, a in opts:
		if o in ("-c", "--connection-string" ):
			url = "uno:" + a + ";urp;StarOffice.ComponentContext"
		if o == "--PDF":
			filterName = "writer_pdf_Export"
			extension = "pdf"
		if o == "--MSXP":
			filterName = "MS Word 97"
			extension = "doc"
		if o == "--MS95":
			filterName = "MS Word 95"
			extension = "doc"
		if o == "--MS60":
			filterName = "MS WinWord 6.0"
			extension = "doc"
		if o == "--RTF":
			filterName = "Rich Text Format"
			extension = "rtf"
		if o == "--HTML":
			filterName = "HTML (StarWriter)"
			extension = "html"
		if o == "--SW5":
			filterName = "StarWriter 5.0"
			extension = "sdw"
		if o == "--SW4":
			filterName = "StarWriter 4.0"
			extension = "sdw"
		if o == "--SW3":
			filterName = "StarWriter 3.0"
			extension = "sdw"
		if o == "--TXT":
			filterName = "Text"
			extension = "txt"
		if o == "--TXTE":
			filterName = "Text (Encoded)"
			extension = "txt"
		if o == "--TEX":
			filterName = "Latex File"
			extension = "tex"
		if o == "--XHTML10":
			filterName = "XHTML 1.0 strict File"
			extension = "html"
		if o == "--XHTML11":
			filterName = "XHTML 1.1 plus MathML 2.0 File"
			extension = "xhtml"

        ctxLocal = uno.getComponentContext()
        smgrLocal = ctxLocal.ServiceManager

        resolver = smgrLocal.createInstanceWithContext(
                 "com.sun.star.bridge.UnoUrlResolver", ctxLocal )
        ctx = resolver.resolve( url )
        smgr = ctx.ServiceManager

        desktop = smgr.createInstanceWithContext("com.sun.star.frame.Desktop", ctx )

        cwd = systemPathToFileUrl( getcwd() )
        outProps = (
            PropertyValue( "FilterName" , 0, filterName , 0 ),
            )
        inProps = PropertyValue( "Hidden" , 0 , True, 0 ),
        for path in args:
		try:
			fileUrl = uno.absolutize( cwd, systemPathToFileUrl(path + ".sxw") )
			doc = desktop.loadComponentFromURL( fileUrl , "_blank", 0,inProps)

			if not doc:
				raise UnoException( "Couldn't open " + fileUrl, None )

			saveUrl = uno.absolutize( cwd, systemPathToFileUrl(path) )
			doc.storeToURL(saveUrl + "." + extension ,outProps)
        
		except IOException, e:
			sys.stderr.write( "Error during conversion: " + e.Message + "\n" )
			retVal = 1
		except UnoException, e:
			sys.stderr.write( "Error ("+repr(e.__class__)+") during conversion:" + e.Message + "\n" )
			retVal = 1
	if doc:
		doc.dispose()

except UnoException, e:
	sys.stderr.write( "Error ("+repr(e.__class__)+") :" + e.Message + "\n" )
	retVal = 1
except getopt.GetoptError,e:
	sys.stderr.write( str(e) + "\n" )
	retVal = 1

sys.exit(retVal)


/*
   Client side JavaScript object for tokenization of a string.
   Best used for something as simple as a comma separated record of values.

   Sample usage:

   <script type="text/javascript" language="javascript" src="../lib/stringTokenizer.js"></script>
   <script type="text/javascript" language="javascript">

   	var separator = ",";
   	var names = "one,two,three";

   	var tokenizer = new StringTokenizer (names, separator);

   	while (tokenizer.hasMoreTokens())
   	{
   		document.write("<p>Name " + tokenizer.nextToken() + "</p>");
   	}  // end while

   </script>

Edited 27/09/2004 11:26AM
   Added a trim function and fixed a few "this"
   references that were not there and should have
   been.
Edited 14/02/2005 9:33PM
   Thanks to Cliff Hale for this!
   getTokens() is dropping the last token in the string if the
   last token is only 1 char in length (e.g.,  "1,2,3" would result
   in it returning "1,2")  To remedy this, I made the following change:   ....
   // Go through material, token at a time.
    while (this.material.length - start >= 1)

   Also changed the while in getTokens to skip over repeating instances
   of the separator.

*/



/*
   Constructor.
   Split up a material string based upong the separator.

   Param    -  material, the String to be split up.
   Param    -  separator, the String to look for within material. Should be
               something like "," or ".", not a regular expression.

*/
function StringTokenizer (material, separator)
{
   // Attributes.
   this.material = material;
   this.separator = separator;

   // Operations.
   this.getTokens = getTokens;
   this.nextToken = nextToken;
   this.countTokens = countTokens;
   this.hasMoreTokens = hasMoreTokens;
   this.tokensReturned = tokensReturned;

   // Initialisation code.
   this.tokens = this.getTokens();
   this.tokensReturned = 0;

}  // end constructor




/*
   Go through material, putting each token into a new array.

   Return      - the array with all the tokens in it.
*/
function getTokens()
{
   // Create array of tokens.
   var tokens = new Array();
   var nextToken;

   // If no separators found, single token is the material string itself.
	if (this.material.indexOf (this.separator) < 0)
	{
		tokens [0] = this.material;
		return tokens;
	}  // end if

   // Establish initial start and end positions of the first token.
   start = 0;
   end = this.material.indexOf (this.separator, start);

   // Counter for how many tokens were found.
   var counter = 0;

   // Go through material, token at a time.
   var trimmed;
 	while (this.material.length - start >= 1)
	{
		nextToken = this.material.substring (start, end);
		start = end + 1;
		if (this.material.indexOf (this.separator, start + 1) < 0)
		{
			end = this.material.length;
		}  // end if
		else
		{
			end = this.material.indexOf (this.separator, start + 1);
		}  // end else

      trimmed = trim (nextToken);

      // Remove any extra separators at start.
      while (trimmed.substring(0, this.separator.length) == this.separator) {
         trimmed = trimmed.substring (this.separator.length);
      }
      trimmed = trim(trimmed);
      if (trimmed == "") {
         continue;
      }
      tokens [counter] = trimmed;
		counter ++;
	}   // end if

   // Return the initialised array.
   return tokens;


}  // end getTokens function


/*
   Return a count of the number of tokens in the material.

   Return      - int number of tokens in material.
*/
function countTokens()
{
  return this.tokens.length;
}  // end countTokens function



/*
   Get next token in material.

   Return      - next token in material.
*/
function nextToken()
{

   if (this.tokensReturned >= this.tokens.length)
   {
      return null;
   }  // end if
   else
   {
      var returnToken = this.tokens [this.tokensReturned];
      this.tokensReturned ++;
      return returnToken;
   }  // end else

}  // end nextToken function



/*
   Tests if there are more tokens available from this tokenizer's string. If
   this method returns true, then a subsequent call to nextToken
   will successfully return a token.

   Return      true if more tokens, false otherwise.
*/
function hasMoreTokens()
{
   if (this.tokensReturned < this.tokens.length)
   {
      return true;
   }  // end if
   else
   {
      return false;
   }  // end else
}  // end hasMoreTokens function

function tokensReturned()
{
   return this.tokensReturned;
}  // end tokensReturned function


function trim (strToTrim) {
   return(strToTrim.replace(/^\s+|\s+$/g, ''));
}  // end trim function



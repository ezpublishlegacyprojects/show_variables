Overview:
----------
Shows the available variables with their namespaces. The output is roughly 
similar to the one created by the attribute operator. Please note that this
is  not fully tested yet - I appreciate every feedback I get.

Usage:
--------
show_variables([info_text[, level [, print_debug [, as_html ] ] ] ] )
Parameters:
- info_text (string): text to be displayed at the variable listing. 
Will be the legend of the fieldset if as_html is set to true, otherwise 
just a separate text line. Suggestion: use the name of the current template.
- level (integer): number of levels that the variables should be expanded. 
Default is 1.
- print_debug (boolean): whether to output the result where the operator 
was called or in the debug area. If set to true, the output will be text 
as html tags are displayed in the debug area. Default is false.
- as_html (boolean): whether to display the output in an html table or as 
text. Default is true.


Known problems
----------------
I am not to clear on the differences between the root namespace and 
the global namespace. I also never introduce namespaces in my templates, 
so I haven't tested this operator with different namespaces much. 
If there are any problems, please contact me with the details 
(template structure, expected output, received output...)


Tested eZpublish versions
--------------------------
3.8.3, 3.9.0


License
--------
GPL 2


Contact
-------
ckosny@gmx.net
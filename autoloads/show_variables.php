<?php

include_once("lib/ezutils/classes/ezdebug.php");

class showVariables
{

   var $Operators;

   /*!
    Constructor
   */
   function showVariables()
   {
       $this->Operators = array('show_variables');
   }

   /*!
    Returns the operators in this class.
   */
   function &operatorList()
   {
       return $this->Operators;
   }

   /*!
   return true to tell the template engine that the parameter list
   exists per operator type, this is needed for operator classes
   that have multiple operators.
   */
   function namedParameterPerOperator()
   {
       return true;
   }

   /*!
    See eZTemplateOperator::namedParameterList()
   */
   function namedParameterList()
   {
       return array('show_variables' => array('info_text' => array( 'type' => 'string',
                                                                'required' => false,
                                                                'default' => false),
                                                 'max_level' => array( 'type' => 'numerical',
                                                                'required' => false,
                                                                'default' => 1),
                                                 'print_debug' => array( 'type' => 'boolean',
                                                                'required' => false,
                                                                'default' => false),
                                                 'as_html' => array( 'type' => 'boolean',
                                                                'required' => false,
                                                                'default' => true)));

   }

   /*!
    Executes the needed operator(s).
    Checks operator names, and calls the appropriate functions.
   */
   function modify( &$tpl, &$operatorName, &$operatorParameters, &$rootNamespace,
                    &$currentNamespace, &$operatorValue, &$namedParameters )
   {
       switch ( $operatorName )
       {
           case 'show_variables':
               $operatorValue = $this->show_variables($tpl,
                                                         $rootNamespace,
                                                         $currentNamespace,
                                                         $namedParameters['info_text'],
                                                         $namedParameters['max_level'],
                                                         $namedParameters['print_debug'],
                                                         $namedParameters['as_html']);
           break;
       }
   }

   /*!
    The actual functions
   */

   function show_variables(&$tpl, &$rootNamespace, &$currentNamespace, $infoText, $maxLevel, $printDebug, $asHtml)
   {
     //html is not possible in debug output as it runs htmlentities
     if($printDebug)
     {
       $asHtml = false;
     }

     $resultStr = '';
     //if we have an infoText, add an fieldset around the tables or textline before rest
     if($infoText)
     {
        if ($asHtml)
        {
          $resultStr .= '<fieldset><legend>' . $infoText . '</legend>' . "\n";
        }
        else
        {
          $resultStr = $infoText . "\n=====================\n";
        }
     }

     /*
       the variables are the keys in the second level of the tpl->Variables array
       the keys on the first level are the namespace
     */
     foreach($tpl->Variables as $namespace=> $varArr)
     {
        //we don't need the namespaces without variables
        if(!count($varArr))
        {
          continue;
        }
        if(!$namespace)
        {
          if($currentNamespace)
          {
            $namespace= ' - GLOBAL - ';
          }
          else
          {
            $namespace= ' - ROOT - ';
          }
        }
        elseif($namespace == $currentNamespace)
        {
          $namespace .= ' (current) ';
        }
        ksort($varArr);
        $txt = '';
        $this->displayVariable($varArr, $asHtml, true, $maxLevel, 0, $txt);

        if ($asHtml)
        {
          $headers  = "<tr><th colspan=\"3\" align=\"left\">Namespace: " . $namespace. "</th>\n</tr>\n";
          $headers .= "<tr><th>Variable/Attribute</th>\n<th>Type</th>\n<th>Value</th>\n</tr>\n";

          $resultStr .= "<table style=\"text-align:left\">$headers$txt</table><br />\n";
        }
        else
        {
          $resultStr .= 'Namespace ' . $namespace. "\n---------------------------\n";
          $resultStr .= $txt . "\n";
        }
     }

     if($infoText && $asHtml)
     {
       $resultStr .= '</fieldset>' . "\n";
     }

     if($printDebug)
     {
       eZDebug::writeDebug(print_r($resultStr, true), 'show_variables');
     }
     else
     {
       return $resultStr;
     }
   }


/*
 The following function is based on the original displayVariable function
 in the eZ class eztemplateattributeoperator.php.
 Changes:
 - put code to determine type in separate function
 - put check for is_string before is_numeric to get quotation marks around
   numeric strings
 - text output now displays $itemValue instead of $item
 - added a few lines to display scalar values as well
 - added a few lines to display info when value is NULL
*/

    function displayVariable( &$value, $as_html, $show_values, $max, $cur_level, &$txt )
    {
        if ($max !== false && $cur_level >= $max)
        {
          return;
        }
        if (is_array($value))
        {
            foreach( $value as $key => $item )
            {
                $this->getTypeInfo($key, $item, $as_html, $show_values, $cur_level, $txt);
                $this->displayVariable( $item, $as_html, $show_values, $max, $cur_level + 1, $txt );
            }
        }
        else if ( is_object( $value ) )
        {
            if ( !method_exists( $value, "attributes" ) or
                 !method_exists( $value, "attribute" ) )
            {
                return;
            }
            $attrs = $value->attributes();
            foreach ( $attrs as $key )
            {
                $item =& $value->attribute( $key );
                $this->getTypeInfo($key, $item, $as_html, $show_values, $cur_level, $txt);
                $this->displayVariable( $item, $as_html, $show_values, $max, $cur_level + 1, $txt );
            }
        }
//*********************************** added  ***********************************//
        //if we have a scalar value which is not part of an array or object
        elseif(is_scalar($value) && $cur_level === 0)
        {
            //this way we can use a direct copy of the display for array items
            $key = ' - scalar variable - ';
            $item = $value;
            $this->getTypeInfo($key, $item, $as_html, $show_values, $cur_level, $txt);
        }
        elseif(is_null($value) && $cur_level === 0)
        {
            if ( $as_html )
            {
                $spacing = str_repeat( ">", $cur_level );
                if ( $show_values )
                    $txt .= "<tr><td colspan=\"3\">$spacing - variable is NULL - </td>\n</tr>\n";
                else
                    $txt .= "<tr><td colspan=\"2\">$spacing - variable is NULL - </td>\n</tr>\n";
            }
            else
            {
                $spacing = str_repeat( " ", $cur_level*4 );
                if ( $show_values )
                    $txt .= "$spacing - variable is NULL - \n";
                else
                    $txt .= "$spacing - variable is NULL - \n";
            }
        }
//*********************************** end of added  ***********************************//
    }

    function getTypeInfo($key, $item, $as_html, $show_values, $cur_level, &$txt )
    {
      $type = gettype( $item );
      if ( is_object( $item ) )
          $type .= "[" . get_class( $item ) . "]";
      $itemValue = $item;
      if ( is_bool( $item ) )
          $itemValue = $item ? "true" : "false";
      else if ( is_array( $item ) )
          $itemValue = 'Array(' . count( $item ) . ')';
      else if ( is_string( $item ) )
          $itemValue = "'" . $item . "'";
      else if ( is_numeric( $item ) )
          $itemValue = $item;
      if ( $as_html )
      {
          $spacing = str_repeat( ">", $cur_level );
          if ( $show_values )
              $txt .= "<tr><td>$spacing$key</td>\n<td>$type</td>\n<td>$itemValue</td>\n</tr>\n";
          else
              $txt .= "<tr><td>$spacing$key</td>\n<td>$type</td>\n</tr>\n";
      }
      else
      {
          $spacing = str_repeat( " ", $cur_level*4 );
          if ( $show_values )
              $txt .= "$spacing$key ($type = $itemValue)\n";
          else
              $txt .= "$spacing$key ($type)\n";
      }
    }
}
?>
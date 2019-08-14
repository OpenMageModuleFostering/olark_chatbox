<?php
/**
 * Widget that adds Olark Live Chat to Magento stores.
 *
 * NOTICE OF LICENSE
 *
 * This source file is subject to the Academic Free License (AFL 3.0)
 * that is bundled with this package in the file LICENSE_AFL.txt.
 * It is also available through the world-wide-web at this URL:
 * http://opensource.org/licenses/afl-3.0.php
 * If you did not receive a copy of the license and are unable to
 * obtain it through the world-wide-web, please send an email
 * to support@olark.com so we can send you a copy immediately.
 *
 * @category    Olark
 * @package     Olark_Chatbox
 * @copyright   Copyright 2012. Habla, Inc. (http://www.olark.com)
 * @license     http://opensource.org/licenses/afl-3.0.php  Academic Free License (AFL 3.0)
 */

class Olark_Chatbox_Block_Chatbox
    extends Mage_Core_Block_Abstract
    implements Mage_Widget_Block_Interface
{

    /**
     * Produces Olark Chatbox html
     *
     * @return string
     */
    protected function _toHtml()
    {
        $customer = $products = array();
        $totalValue = $totalItems = 0;
        
        // don't show the Olark code if there is no account number
        $siteID = $this->getData('siteID');
        if (empty($siteID)) {
            return '';
        }
        
        // build customer array
        $info = Mage::getSingleton('customer/session')->getCustomer();
        if ($info) {
        
          $billingAddress = $info->getPrimaryBillingAddress();
          if( $billingAddress ) $billingAddress = $billingAddress->format('text');

          $shippingAddress = $info->getPrimaryShippingAddress();
          if( $shippingAddress ) $shippingAddress = $shippingAddress->format('text');

          $customer = array( 
            'name'      => $info->getName(),
            'email'     => $info->getEmail(),
            'billing'   => $billingAddress,
            'shipping'  => $shippingAddress
          );
        }        

        // build cart and total arrays
        $items = Mage::getSingleton('checkout/session')->getQuote()->getAllItems();
        if ($items) {
          $totalValue = $totalItems = 0;
          foreach($items as $item) {
            $product = array(
            	'name' => $item->getName(),
            	'sku'     => $item->getSku(),
            	'quantity'     => $item->getQty(),
            	'price'   => $item->getPrice()
            );
            $products[] = $product;
            $totalItems = $totalItems + $product['quantity'];
            $totalValue = $totalValue + ($product['price'] *  $product['quantity']);
          }
        }

        // build the html & javascript string
        $html = '
        <!-- begin olark code --><script type=\'text/javascript\'>/*{literal}<![CDATA[*/
          window.olark||(function(c){var f=window,d=document,l=f.location.protocol=="https:"?"https:":"http:",z=c.name,r="load";var nt=function(){f[z]=function(){(a.s=a.s||[]).push(arguments)};var a=f[z]._={},q=c.methods.length;while(q--){(function(n){f[z][n]=function(){f[z]("call",n,arguments)}})(c.methods[q])}a.l=c.loader;a.i=nt;a.p={0:+new Date};a.P=function(u){a.p[u]=new Date-a.p[0]};function s(){a.P(r);f[z](r)}f.addEventListener?f.addEventListener(r,s,false):f.attachEvent("on"+r,s);var ld=function(){function p(hd){hd="head";return["<",hd,"></",hd,"><",i,\' onl\' + \'oad="var d=\',g,";d.getElementsByTagName(\'head\')[0].",j,"(d.",h,"(\'script\')).",k,"=\'",l,"//",a.l,"\'",\'"\',"></",i,">"].join("")}var i="body",m=d[i];if(!m){return setTimeout(ld,100)}a.P(1);var j="appendChild",h="createElement",k="src",n=d[h]("div"),v=n[j](d[h](z)),b=d[h]("iframe"),g="document",e="domain",o;n.style.display="none";m.insertBefore(n,m.firstChild).id=z;b.frameBorder="0";b.id=z+"-loader";if(/MSIE[ ]+6/.test(navigator.userAgent)){b.src="javascript:false"}b.allowTransparency="true";v[j](b);try{b.contentWindow[g].open()}catch(w){c[e]=d[e];o="javascript:var d="+g+".open();d.domain=\'"+d.domain+"\';";b[k]=o+"void(0);"}try{var t=b.contentWindow[g];t.write(p());t.close()}catch(x){b[k]=o+\'d.write("\'+p().replace(/"/g,String.fromCharCode(92)+\'"\')+\'");d.close();\'}a.P(2)};ld()};nt()})({loader: "static.olark.com/jsclient/loader0.js",name:"olark",methods:["configure","extend","declare","identify"]});
          /* custom configuration goes here (www.olark.com/documentation) */
          olark.identify(\''.$siteID.'\'); /*]]>{/literal}*/</script>
          <noscript><a href="https://www.olark.com/site/'.$siteID.'/contact" title="Contact us" target="_blank">Questions? Feedback?</a> powered by <a href="http://www.olark.com?welcome" title="Olark live chat software">Olark live chat software</a></noscript>
        <!-- olark magento cart saver --> 
        <script type=\'text/javascript\'>
          olark.extend(\'Magento\');
          olark.configure(\'Magento.items\', '.json_encode($totalItems).');
          olark.configure(\'Magento.total\', '.json_encode($totalValue).');
          olark.configure(\'Magento.products\', '.json_encode($products).');
          olark.configure(\'Magento.customer\', '.json_encode($customer).');
          olark.configure(\'Magento.valueThreshold\', '.json_encode($this->getData('valueThreshold')).');
          olark.configure(\'Magento.removeNotification\', '.json_encode(!!$this->getData('removeNotification')).');
          olark.configure(\'Magento.addNotification\', '.json_encode(!!$this->getData('addNotification')).');
          olark.configure(\'Magento.showSku\', '.json_encode(!!$this->getData('showSku')).');
          olark.configure(\'Magento.version\', \'0.0.1\');
        </script>
        <!-- custom olark config -->
        '.$this->getData('customConfig');

        return $html;
    }

}

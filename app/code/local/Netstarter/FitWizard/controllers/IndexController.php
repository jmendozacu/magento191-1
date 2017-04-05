<?php
/**
 * Created by JetBrains PhpStorm.
 * Company: http://www.netstarter.com.au
 * Licence: http://www.netstarter.com.au
 * Date: 6/4/14
 * Time: 4:01 PM
 */
class Netstarter_FitWizard_IndexController extends Mage_Core_Controller_Front_Action
{

    public function indexAction()
    {
        $this->loadLayout();
        $this->renderLayout();
    }

    public function separationAction()
    {

        if ($this->getRequest()->isPost()) {
            $postData = $this->getRequest()->getPost('data');

            Mage::getSingleton('core/session')->setSearchWizardData($postData);
            $this->_redirect('*/*/fullness');
            return;
        }
        $this->loadLayout();
        $this->renderLayout();
    }

    public function fullnessAction()
    {
        if ($this->getRequest()->isPost()) {
            $postData = $this->getRequest()->getPost('data');

            Mage::getSingleton('core/session')->setSearchWizardData($postData);
            $this->_redirect('*/*/position');
            return;
        }
        $this->loadLayout();
        $this->renderLayout();
    }


    public function positionAction()
    {
        if ($this->getRequest()->isPost()) {
            $postData = $this->getRequest()->getPost('data');

            Mage::getSingleton('core/session')->setSearchWizardData($postData);
            $this->_redirect('*/*/getemail');
            return;
        }
        $this->loadLayout();
        $this->renderLayout();
    }


    public function getemailAction()
    {
        if ($this->getRequest()->isPost()) {
            $postData = $this->getRequest()->getPost('data');

            Mage::getSingleton('core/session')->setSearchWizardData($postData);
            $this->_redirect('*/*/results');
            return;
        }
        $this->loadLayout();
        $this->renderLayout();
        //Mage::log ('getemail',null,'bra-finder.log' );
    }

    public function resultsAction() {
        $this->loadLayout();

        try {
            $response = array();
            $block = $this->getLayout()->getBlock('fitwizard_result_page');
            $response['results'] = $block->toHtml();
            $postData = Mage::getSingleton('core/session')->getData('fitwizard_data');
            $email=$postData['email'];
            //Mage::log ($postData,null,'bra-finder.log' );
            # change status to "subscribed" and save
            //Mage::log ($email,null,'bra-finder.log' );

            $subscriber = Mage::getModel('newsletter/subscriber')->loadByEmail($email);
            $resource = Mage::getSingleton('core/resource');
            /**
             * Retrieve the read connection
             */
            $readConnection = $resource->getConnection('core_read');
            $query = 'SELECT value from eav_attribute_option_value where store_id=0 and option_id='.$postData['size'] ;
            /**
             * Execute the query and store the results in $results
             */
            $sizename = $readConnection->fetchCol($query);
          //  Mage::log ($sizename,null,'bra-finder.log' );
            //Mage::log ($subscriber,null,'bra-finder.log' );
            //Mage::log ('result',null,'bra-finder.log' );
            if ($response['results']) {
                if ($subscriber['subscriber_email'])
                {
                    //Mage::log ('subscriber available',null,'bra-finder.log' );
                $subscriber->setData('bra_size',$sizename[0]);
                if ($postData['cup']=='1')
                {
                    $subscriber->setData('push-up',$postData['cup']);
                }
                if ($postData['cup']=='2')
                {
                    $subscriber->setData('Contour',$postData['cup']);
                }
                if ($postData['cup']=='3')
                {
                    $subscriber->setData('no_padding', $postData['cup']);
                }
                $subscriber->setData('fullness', $postData['fullness']);
                $subscriber->setData('position', $postData['position']);
                //$subscriber->setData('subscription_date', Varien_Date::now());
                $subscriber->save();
                }
                else
                {
                    //Mage::log ('new subscriber',null,'bra-finder.log' );
                    $subscriber->setData('subscriber_email', $postData['email']);
                    $subscriber->setData('bra_size', $sizename[0]);
                    $subscriber->setData('subscription_date', Varien_Date::now());

                    if ($postData['cup']=='1')
                    {
                        $subscriber->setData('push-up',$postData['cup']);
                    }
                    if ($postData['cup']=='2')
                    {
                        $subscriber->setData('Contour',$postData['cup']);
                    }
                    if ($postData['cup']=='3')
                    {
                        $subscriber->setData('no_padding', $postData['cup']);
                    }
                    $subscriber->setStatus(Mage_Newsletter_Model_Subscriber::STATUS_SUBSCRIBED);
                    $subscriber->setData('fullness', $postData['fullness']);
                    $subscriber->setData('position', $postData['position']);
                    //$subscriber->setData('subscription_date', Varien_Date::now());
                    $subscriber->save();

                }
                $block->sendEmailNotificationEmail();
            }

            if ($this->getRequest()->isPost()) {
                $response['recommended'] = $this->getLayout()->getBlock('fitwizard_recommended_categories')->toHtml();
                //Mage::log ('result-post',null,'bra-finder.log' );
                /*$post = $this->getRequest()->getPost();

                if (!empty($post)) {

                    //Mage::getModel('newsletter/subscriber')->setImportMode(true)->subscribe($email);

                    # get just generated subscriber
                    $this->saveSubscriber($this->getRequest()->getPost('email'));
                    $subscriber = Mage::getModel('newsletter/subscriber')->loadByEmail($this->getRequest()->getPost('email'));
                    $id=$subscriber->getData('subscriber_id');
                    if($id!='')
                    {
                        //$subscriber->setData('subscriber_email', $this->getRequest()->getPost('email'));
                        $subscriber->setData('bra_size', $this->getRequest()->getPost('size'));
                        if ($this->getRequest()->getPost('cup')=='1')
                        {
                            $subscriber->setData('push-up',$this->getRequest()->getPost('cup'));
                        }
                        if ($this->getRequest()->getPost('cup')=='2')
                        {
                            $subscriber->setData('Contour', $this->getRequest()->getPost('cup'));
                        }
                        if ($this->getRequest()->getPost('cup')=='3')
                        {
                            $subscriber->setData('no_padding', $this->getRequest()->getPost('cup'));
                        }
                        $subscriber->setData('fullness', $this->getRequest()->getPost('fullness'));
                        $subscriber->setData('position', $this->getRequest()->getPost('position'));
                        //$subscriber->setData('subscription_date', Varien_Date::now());
                        $subscriber->setId($id)->save();
                    }
                }*/
            }
            $this->getResponse()->clearHeaders()->setBody(Mage::helper('core')->jsonEncode($response));
        } catch (Exception $e) {

        }
    }
}
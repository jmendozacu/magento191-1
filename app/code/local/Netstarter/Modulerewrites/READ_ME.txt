Generic module override module.

PURPOSE: For minor changes, rather than creating a module for each change, modulerewrite work as a common module for those changes.
This would simplify the code structure and reduce the overhead of bulk module hierarchy.

//////////////////////

<newsletter>
    <rewrite>
        <subscriber>Netstarter_Modulerewrites_Model_Newsletter_Subscriber</subscriber>
    </rewrite>
</newsletter>


Overridden subscribe functions: if the user is already subscribed and confirmed no need to send another confirmation email, add subscribed date
Overridden subscribe functions: add subscribed date

<adminhtml>
    <rewrite>
        <newsletter_subscriber_grid>Netstarter_Modulerewrites_Block_Adminhtml_Newsletter_Subscriber_Grid</newsletter_subscriber_grid>
    </rewrite>
</adminhtml>

added custom column to grid
////////////////////////

A path issue detected in schedule export

<enterprise_importexport>
    <rewrite>
        <scheduled_operation>Netstarter_Modulerewrites_Model_ImportExport_Scheduled_Operation</scheduled_operation>
    </rewrite>
</enterprise_importexport>
<div class="cmplz-section-content">

    <form action="{page_url}" method="POST">
		<input type="hidden" value="{step}" name="step">
		<input type="hidden" value="{section}" name="section">
		<?php wp_nonce_field( 'complianz_tc_save', 'complianz_tc_nonce' ); ?>

        <div class="cmplz-wizard-title cmplz-section-content-title-header">
			<h1>{title}</h1>
			{flags}
		</div>
        <div class="cmplz-wizard-title cmplz-section-content-notifications-header">
			<h1><?php _e("Notifications", "complianz-gdpr")?></h1>
		</div>
	    {learn_notice}
	    {intro}
		{post_id}

		{fields}

        <div class="cmplz-section-footer">
            {save_as_notice}
            {save_notice}
            <div class="cmplz-buttons-container">
                {previous_button}
                {save_button}
                {next_button}
                {cookie_or_finish_button}
            </div>
        </div>

    </form>

</div>


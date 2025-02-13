<section class="contact-section sec-pad" style="display: flex; justify-content: center; align-items: center; background-color: #f9f9f9;">
    <div class="auto-container">
        <div class="row clearfix">
            <div class="col-lg-12 col-md-12 col-sm-12 form-column" style="display: flex; justify-content: center;">
                <div class="form-inner" style="background: #ffffff; border: 1px solid #ccc; border-radius: 8px; padding: 30px; box-shadow: 0px 4px 6px rgba(0,0,0,0.1); width: 100%;">
                    <form method="post" action="{{ route('contact.send') }}" id="contact-form">
                        @csrf
                        <div class="row clearfix">
                            <div class="col-lg-6 col-md-6 col-sm-12 form-group">
                                <input type="text" name="username" placeholder="@lang('messages.your_name')" required style="border: 1px solid #ccc; padding: 10px; width: 100%;">
                            </div>
                            <div class="col-lg-6 col-md-6 col-sm-12 form-group">
                                <input type="email" name="email" placeholder="@lang('messages.your_email')" required style="border: 1px solid #ccc; padding: 10px; width: 100%;">
                            </div>
                            <div class="col-lg-12 col-md-12 col-sm-12 form-group">
                                <input type="text" name="phone" required placeholder="@lang('messages.your_phone')" style="border: 1px solid #ccc; padding: 10px; width: 100%;">
                            </div>
                            <div class="col-lg-12 col-md-12 col-sm-12 form-group">
                                <textarea name="message" placeholder="@lang('messages.your_message')" style="border: 1px solid #ccc; padding: 10px; width: 100%; height: 120px;"></textarea>
                            </div>
                            <div class="col-lg-12 col-md-12 col-sm-12 form-group message-btn mr-0" style="text-align: center;">
                                <button class="theme-btn-one" type="submit" name="submit-form">
                                    <span>@lang('messages.send_message')</span>
                                </button>
                            </div>
                        </div>
                    </form>
                </div>
            </div>
        </div>
    </div>
</section>

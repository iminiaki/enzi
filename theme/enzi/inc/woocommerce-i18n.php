<?php
/**
 * WooCommerce Persian string translations.
 *
 * @package Diako
 */

if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

/**
 * WooCommerce string translations (singular).
 *
 * @return array<string, string>
 */
function diako_woocommerce_gettext_map() {
	return array(
		// Shop & catalog.
		'Shop'                                            => 'فروشگاه',
		'Home'                                            => 'خانه',
		'Checkout'                                        => 'تسویه حساب',
		'Showing the single result'                       => 'نمایش 1 محصول',
		'Showing %1$d&ndash;%2$d of %3$d results'         => 'نمایش %1$d تا %2$d از %3$d محصول',
		'Showing %1$d–%2$d of %3$d results'              => 'نمایش %1$d تا %2$d از %3$d محصول',
		'Default sorting'                                 => 'مرتب‌سازی پیش‌فرض',
		'Default'                                         => 'پیش‌فرض',
		'Sort by popularity'                              => 'مرتب‌سازی بر اساس محبوبیت',
		'Sort by average rating'                          => 'مرتب‌سازی بر اساس امتیاز',
		'Sort by latest'                                  => 'جدیدترین',
		'Sort by price: low to high'                      => 'ارزان‌ترین',
		'Sort by price: high to low'                      => 'گران‌ترین',
		'Sorted by popularity'                            => 'مرتب‌شده بر اساس محبوبیت',
		'Sorted by average rating'                        => 'مرتب‌شده بر اساس امتیاز',
		'Sorted by latest'                                => 'مرتب‌شده بر اساس جدیدترین',
		'Sorted by price: low to high'                    => 'مرتب‌شده از ارزان به گران',
		'Sorted by price: high to low'                    => 'مرتب‌شده از گران به ارزان',
		'Popularity'                                      => 'محبوبیت',
		'Latest'                                          => 'جدیدترین',
		'Relevance'                                       => 'مرتبط‌ترین',
		'Search results: &ldquo;%s&rdquo;'                => 'جستجو: %s',
		'Description'                                     => 'توضیحات',
		'Additional information'                          => 'مشخصات',
		'Reviews'                                         => 'نظرات',
		'Reviews (%d)'                                    => 'نظرات (%d)',
		'Related products'                                => 'محصولات مرتبط',
		'You may also like&hellip;'                       => 'محصولات پیشنهادی',
		'You may be interested in&hellip;'                => 'پیشنهادهای همراه',
		'Average rating'                                  => 'میانگین امتیاز',
		'Rated %s out of 5'                               => 'امتیاز %s از 5',
		'No products were found matching your selection.' => 'محصولی مطابق انتخاب شما یافت نشد.',
		'Filter'                                          => 'اعمال فیلتر',
		'Price:'                                          => 'قیمت:',
		'Active filters'                                  => 'فیلترهای فعال',
		'Remove %s filter'                                => 'حذف فیلتر %s',
		'Remove this item'                                => 'حذف این مورد',
		'There are no reviews yet.'                       => 'هنوز نظری ثبت نشده است.',
		'Add a review'                                    => 'ثبت نظر',
		'Your rating'                                     => 'امتیاز شما',
		'Your review'                                     => 'نظر شما',
		'Submit'                                          => 'ارسال',
		'SKU:'                                            => 'شناسه محصول:',
		'N/A'                                             => 'ندارد',
		'Category:'                                       => 'دسته‌بندی:',
		'Categories:'                                     => 'دسته‌بندی‌ها:',
		'Tag:'                                            => 'برچسب:',
		'Tags:'                                           => 'برچسب‌ها:',
		'Weight'                                          => 'وزن',
		'Dimensions'                                      => 'ابعاد',
		'Free!'                                           => 'رایگان!',

		// Account.
		'Dashboard'                                       => 'پیشخوان',
		'Orders'                                          => 'سفارش‌ها',
		'Downloads'                                       => 'دانلودها',
		'Addresses'                                       => 'آدرس‌ها',
		'Account details'                                 => 'جزئیات حساب',
		'Logout'                                          => 'خروج',
		'Payment methods'                                 => 'روش‌های پرداخت',
		'Login'                                           => 'ورود',
		'Register'                                        => 'ثبت‌نام',
		'Log in'                                          => 'ورود',
		'Username'                                        => 'نام کاربری',
		'Username or email address'                       => 'نام کاربری یا ایمیل',
		'Password'                                        => 'رمز عبور',
		'Remember me'                                     => 'مرا به خاطر بسپار',
		'Lost your password?'                             => 'رمز عبور را فراموش کرده‌اید؟',
		'Email address'                                   => 'ایمیل',
		'First name'                                      => 'نام',
		'Last name'                                       => 'نام خانوادگی',
		'Display name'                                    => 'نام نمایشی',
		'Password change'                                 => 'تغییر رمز عبور',
		'Current password (leave blank to leave unchanged)' => 'رمز عبور فعلی',
		'New password (leave blank to leave unchanged)'   => 'رمز عبور جدید',
		'Confirm new password'                            => 'تکرار رمز عبور جدید',
		'This will be how your name will be displayed in the account section and in reviews' => 'این نام در بخش حساب کاربری و دیدگاه‌ها نمایش داده می‌شود.',
		'Order'                                           => 'سفارش',
		'Date'                                            => 'تاریخ',
		'Status'                                          => 'وضعیت',
		'Total'                                           => 'مجموع',
		'Actions'                                         => 'عملیات',
		'View'                                            => 'مشاهده',
		'Previous'                                        => 'قبلی',
		'Next'                                            => 'بعدی',
		'No order has been made yet.'                     => 'هنوز سفارشی ثبت نکرده‌اید.',
		'No downloads available yet.'                     => 'هنوز فایلی برای دانلود ندارید.',
		'No saved methods found.'                         => 'روش پرداخت ذخیره‌شده‌ای وجود ندارد.',
		'Add payment method'                              => 'افزودن روش پرداخت',
		'Browse products'                                 => 'مشاهده فروشگاه',
		'Billing address'                                 => 'آدرس صورتحساب',
		'Shipping address'                                => 'آدرس ارسال',
		'The following addresses will be used on the checkout page by default.' => 'آدرس‌های زیر برای ارسال فاکتور و صورت حساب استفاده خواهند شد.',
		'Edit %s'                                         => 'ویرایش %s',
		'Add %s'                                          => 'افزودن %s',
		'You have not set up this type of address yet.'    => 'هنوز این آدرس را ثبت نکرده‌اید.',
		'Edit'                                            => 'ویرایش',
		'Add'                                             => 'افزودن',
		'Save changes'                                    => 'ذخیره تغییرات',
		'Save address'                                    => 'ذخیره آدرس',
		'Pay'                                             => 'پرداخت',
		'Cancel'                                          => 'لغو',
		'Account details changed successfully.'           => 'جزئیات حساب با موفقیت به‌روزرسانی شد.',
		'Address changed successfully.'                 => 'آدرس با موفقیت به‌روزرسانی شد.',
		'Payment method deleted.'                         => 'روش پرداخت حذف شد.',
		'Payment method successfully added.'              => 'روش پرداخت با موفقیت افزوده شد.',
		'This payment method was successfully set as your default.' => 'این روش پرداخت به‌عنوان پیش‌فرض تنظیم شد.',
		'Unable to add payment method to your account.'   => 'افزودن روش پرداخت به حساب شما ممکن نیست.',
		'Your account was created successfully and a password has been sent to your email address.' => 'حساب شما ایجاد شد و رمز عبور به ایمیل شما ارسال شد.',
		'Your account was created successfully. Your login details have been sent to your email address.' => 'حساب شما ایجاد شد و اطلاعات ورود به ایمیل شما ارسال شد.',
		'This email address is already registered.'       => 'این ایمیل قبلاً ثبت شده است.',
		'Username is required.'                           => 'نام کاربری الزامی است.',
		'Please enter your password.'                     => 'لطفاً رمز عبور را وارد کنید.',
		'Please enter your current password.'             => 'لطفاً رمز عبور فعلی را وارد کنید.',
		'Please fill out all password fields.'            => 'لطفاً همه فیلدهای رمز عبور را پر کنید.',
		'Please re-enter your password.'                  => 'لطفاً رمز عبور را دوباره وارد کنید.',
		'Passwords do not match.'                         => 'رمزهای عبور یکسان نیستند.',
		'New passwords do not match.'                     => 'رمزهای عبور جدید یکسان نیستند.',
		'Your current password is incorrect.'             => 'رمز عبور فعلی نادرست است.',
		'Please provide a valid email address.'           => 'لطفاً یک ایمیل معتبر وارد کنید.',
		'Your order was cancelled.'                       => 'سفارش شما لغو شد.',
		'Order cancelled by customer.'                    => 'سفارش توسط مشتری لغو شد.',
		'Your order can no longer be cancelled. Please contact us if you need assistance.' => 'دیگر امکان لغو این سفارش وجود ندارد. در صورت نیاز با ما تماس بگیرید.',
		'Invalid order.'                                  => 'سفارش نامعتبر است.',
		'Invalid order'                                   => 'سفارش نامعتبر است',

		// Order tracking.
		'To track your order please enter your Order ID in the box below and press the "Track" button. This was given to you on your receipt and in the confirmation email you should have received.' => 'برای پیگیری سفارش، شماره سفارش و ایمیل صورتحساب را در فرم زیر وارد کنید.',
		'Order ID'                                        => 'شماره سفارش',
		'Billing email'                                   => 'ایمیل صورتحساب',
		'Track'                                           => 'پیگیری',
		'Found in your order confirmation email.'           => 'در ایمیل تأیید سفارش موجود است.',
		'Email you used during checkout.'                 => 'ایمیلی که هنگام خرید وارد کردید.',
		'Please enter a valid order ID'                   => 'لطفاً شماره سفارش معتبر وارد کنید.',
		'Please enter a valid email address'              => 'لطفاً یک ایمیل معتبر وارد کنید.',
		'Sorry, the order could not be found. Please contact us if you are having difficulty finding your order details.' => 'متأسفانه سفارشی با این مشخصات پیدا نشد. در صورت نیاز با پشتیبانی تماس بگیرید.',
		'Order updates'                                   => 'به‌روزرسانی‌های سفارش',
		'Order #%1$s was placed on %2$s and is currently %3$s.' => 'سفارش #%1$s در تاریخ %2$s ثبت شد و وضعیت فعلی آن %3$s است.',
		'Are you sure you want to log out? <a href="%s">Confirm and log out</a>' => 'آیا مطمئن هستید که می‌خواهید خارج شوید؟ <a href="%s">تأیید و خروج</a>',

		// Cart.
		'View cart'                                       => 'سبد خرید',
		'Cart'                                            => 'سبد خرید',
		'Product'                                         => 'محصول',
		'Price'                                           => 'قیمت',
		'Quantity'                                        => 'تعداد',
		'Subtotal'                                        => 'جمع جزء',
		'Subtotal:'                                       => 'جمع جزء:',
		'Update cart'                                     => 'به‌روزرسانی سبد',
		'Apply coupon'                                    => 'اعمال کد تخفیف',
		'Coupon code'                                     => 'کد تخفیف',
		'Coupon:'                                         => 'کد تخفیف:',
		'Coupon: %s'                                      => 'کد تخفیف: %s',
		'Cart totals'                                     => 'خلاصه سفارش',
		'Proceed to checkout'                             => 'ادامه و پرداخت',
		'Your cart is currently empty.'                   => 'سبد خرید شما خالی است.',
		'Return to shop'                                  => 'بازگشت به فروشگاه',
		'Continue shopping'                               => 'ادامه خرید',
		'Remove item'                                     => 'حذف محصول',
		'Shipping'                                        => 'هزینه ارسال',
		'Available on backorder'                          => 'موجود برای پیش‌سفارش',
		'Remove %s from cart'                             => 'حذف %s از سبد',
		'Calculate shipping'                              => 'محاسبه هزینه ارسال',
		'Update totals'                                   => 'به‌روزرسانی',
		'Enter a coupon code'                             => 'کد تخفیف را وارد کنید',
		'Cart updated.'                                   => 'سبد خرید به‌روزرسانی شد.',
		'&ldquo;%s&rdquo; has been added to your cart'    => '«%s» به سبد خرید اضافه شد',
		'You can only have 1 %s in your cart.'            => 'فقط یک عدد از «%s» می‌توانید در سبد داشته باشید.',
		'You cannot add another "%s" to your cart.'       => 'امکان افزودن «%s» بیشتر به سبد وجود ندارد.',
		'You cannot add &quot;%s&quot; to the cart because the product is out of stock.' => 'امکان افزودن «%s» به سبد وجود ندارد؛ این محصول موجود نیست.',
		'You cannot add that amount of &quot;%1$s&quot; to the cart because there is not enough stock (%2$s remaining).' => 'امکان افزودن این تعداد از «%1$s» وجود ندارد؛ فقط %2$s عدد موجود است.',
		'You cannot add that amount to the cart &mdash; we have %1$s in stock and you already have %2$s in your cart.' => 'امکان افزودن این تعداد وجود ندارد — %1$s عدد موجود است و %2$s عدد در سبد شماست.',
		'An item which is no longer available was removed from your cart.' => 'یک محصول ناموجود از سبد شما حذف شد.',
		'The selected product is invalid.'                => 'محصول انتخاب‌شده نامعتبر است.',
		'Please choose product options&hellip;'           => 'لطفاً گزینه‌های محصول را انتخاب کنید…',
		'Please choose product options by visiting <a href="%1$s" title="%2$s">%2$s</a>.' => 'لطفاً با مراجعه به <a href="%1$s" title="%2$s">%2$s</a> گزینه‌های محصول را انتخاب کنید.',
		'Please choose product options for %1$s.'         => 'لطفاً گزینه‌های %1$s را انتخاب کنید.',
		'Please choose a product to add to your cart&hellip;' => 'لطفاً محصولی برای افزودن به سبد انتخاب کنید…',
		'Please choose the quantity of items you wish to add to your cart&hellip;' => 'لطفاً تعداد موردنظر برای افزودن به سبد را انتخاب کنید…',
		'Invalid value posted for %s'                     => 'مقدار نامعتبر برای %s ارسال شد',
		'Item'                                            => 'محصول',
		'%s removed.'                                     => '%s حذف شد.',
		'Undo?'                                           => 'بازگردانی؟',

		// Coupons.
		'Coupon code applied successfully.'               => 'کد تخفیف با موفقیت اعمال شد.',
		'Coupon code removed successfully.'               => 'کد تخفیف با موفقیت حذف شد.',
		'Please enter a coupon code.'                     => 'لطفاً کد تخفیف را وارد کنید.',
		'Coupon does not exist.'                          => 'کد تخفیف وجود ندارد.',
		'Invalid coupon'                                  => 'کد تخفیف نامعتبر است',
		'Invalid coupon.'                                 => 'کد تخفیف نامعتبر است.',
		'Invalid coupon code'                             => 'کد تخفیف نامعتبر است',
		'Coupon has been removed.'                        => 'کد تخفیف حذف شد.',
		'Coupon removed: "%s".'                           => 'کد تخفیف «%s» حذف شد.',
		'Sorry there was a problem removing this coupon.' => 'مشکلی در حذف این کد تخفیف پیش آمد.',
		'Coupon "%s" cannot be applied because it does not exist.' => 'کد تخفیف «%s» وجود ندارد و قابل اعمال نیست.',
		'Coupon "%s" cannot be applied because it is not valid.' => 'کد تخفیف «%s» معتبر نیست و قابل اعمال نیست.',
		'Coupon "%s" has expired.'                        => 'مهلت استفاده از کد تخفیف «%s» به پایان رسیده است.',
		'Coupon code "%s" already applied!'               => 'کد تخفیف «%s» قبلاً اعمال شده است!',
		'Please enter a valid email at checkout to use coupon code "%s".' => 'برای استفاده از کد تخفیف «%s» لطفاً ایمیل معتبر در صفحه تسویه وارد کنید.',
		'Please enter a valid email to use coupon code "%s".' => 'برای استفاده از کد تخفیف «%s» لطفاً ایمیل معتبر وارد کنید.',
		'Sorry, coupon "%1$s" is not applicable to the categories: %2$s.' => 'متأسفانه کد تخفیف «%1$s» برای این دسته‌ها قابل استفاده نیست: %2$s.',
		'Sorry, coupon "%1$s" is not applicable to the products: %2$s.' => 'متأسفانه کد تخفیف «%1$s» برای این محصولات قابل استفاده نیست: %2$s.',
		'Sorry, coupon "%s" has already been applied and cannot be used in conjunction with other coupons.' => 'کد تخفیف «%s» قبلاً اعمال شده و نمی‌توان آن را همراه با کدهای دیگر استفاده کرد.',
		'Sorry, coupon "%s" is not applicable to selected products.' => 'کد تخفیف «%s» برای محصولات انتخاب‌شده قابل استفاده نیست.',
		'Sorry, coupon "%s" is not applicable to your cart contents.' => 'کد تخفیف «%s» برای محصولات سبد شما قابل استفاده نیست.',
		'Sorry, coupon "%s" is not valid for sale items.' => 'کد تخفیف «%s» برای محصولات تخفیف‌دار قابل استفاده نیست.',
		'Sorry, it seems the coupon "%s" is invalid - it has now been removed from your order.' => 'کد تخفیف «%s» نامعتبر بود و از سفارش شما حذف شد.',
		'The maximum spend for coupon "%1$s" is %2$s.'    => 'حداکثر مبلغ خرید برای کد تخفیف «%1$s» برابر %2$s است.',
		'The minimum spend for coupon "%1$s" is %2$s.'    => 'حداقل مبلغ خرید برای کد تخفیف «%1$s» برابر %2$s است.',
		'Usage limit for coupon "%s" has been reached.'   => 'سقف استفاده از کد تخفیف «%s» تکمیل شده است.',
		'Usage limit for coupon "%s" has been reached. Please try again after some time, or contact us for help.' => 'سقف استفاده از کد تخفیف «%s» تکمیل شده است. لطفاً بعداً دوباره تلاش کنید یا با ما تماس بگیرید.',
		'Usage limit for coupon "%1$s" has been reached. If you were using this coupon just now but your order was not complete, you can retry or cancel the order by going to the <a href="%2$s">my account page</a>.' => 'سقف استفاده از کد تخفیف «%1$s» تکمیل شده است. اگر سفارش شما کامل نشده، از <a href="%2$s">صفحه حساب کاربری</a> می‌توانید دوباره تلاش کنید یا سفارش را لغو کنید.',

		// Checkout.
		'Place order'                                     => 'ثبت سفارش',
		'Your order'                                      => 'خلاصه سفارش',
		'Billing details'                                 => 'اطلاعات صورتحساب',
		'Billing &amp; Shipping'                          => 'اطلاعات صورتحساب و ارسال',
		'Shipping details'                                => 'اطلاعات ارسال',
		'Ship to a different address?'                    => 'ارسال به آدرس دیگر؟',
		'Create an account?'                              => 'ایجاد حساب کاربری؟',
		'You must be logged in to checkout.'              => 'برای تسویه حساب باید وارد شوید.',
		'Have a coupon?'                                  => 'کد تخفیف دارید؟',
		'Click here to enter your code'                   => 'کد را اینجا وارد کنید',
		'Enter your coupon code'                          => 'کد تخفیف را وارد کنید',
		'Returning customer?'                             => 'مشتری قبلی هستید؟',
		'Click here to login'                             => 'برای ورود کلیک کنید',
		'If you have shopped with us before, please enter your details below. If you are a new customer, please proceed to the Billing section.' => 'اگر قبلاً از ما خرید کرده‌اید، اطلاعات ورود را وارد کنید. در غیر این صورت به بخش صورتحساب بروید.',
		'Thank you. Your order has been received.'        => 'سپاسگزاریم. سفارش شما ثبت شد.',
		'Order number:'                                   => 'شماره سفارش:',
		'Date:'                                           => 'تاریخ:',
		'Email:'                                          => 'ایمیل:',
		'Total:'                                          => 'مجموع:',
		'Payment method:'                                 => 'روش پرداخت:',
		'Payment method'                                  => 'روش پرداخت',
		'My account'                                      => 'حساب کاربری',
		'Unfortunately your order cannot be processed as the originating bank/merchant has declined your transaction. Please attempt your purchase again.' => 'متأسفانه پرداخت شما توسط بانک یا درگاه رد شد. لطفاً دوباره تلاش کنید.',
		'Sorry, it seems that there are no available payment methods. Please contact us if you require assistance or wish to make alternate arrangements.' => 'در حال حاضر روش پرداختی در دسترس نیست. لطفاً با ما تماس بگیرید.',
		'Please fill in your details above to see available payment methods.' => 'برای مشاهده روش‌های پرداخت، ابتدا اطلاعات بالا را تکمیل کنید.',
		'Since your browser does not support JavaScript, or it is disabled, please ensure you click the %1$sUpdate Totals%2$s button before placing your order. You may be charged more than the amount stated above if you fail to do so.' => 'مرورگر شما جاوااسکریپت را پشتیبانی نمی‌کند. قبل از ثبت سفارش حتماً %1$sبه‌روزرسانی%2$s را بزنید.',
		'Order notes'                                     => 'یادداشت سفارش',
		'optional'                                        => 'اختیاری',
		'Create account password'                         => 'رمز عبور حساب',
		'Account username'                                => 'نام کاربری',
		'Select a country / region&hellip;'               => 'کشور / منطقه را انتخاب کنید…',
		'Select an option&hellip;'                        => 'یک گزینه انتخاب کنید…',
		'Choose an option'                                => 'گزینه‌ای انتخاب کنید',
		'Update country / region'                         => 'به‌روزرسانی کشور / منطقه',
		'Please enter an address to continue.'            => 'لطفاً برای ادامه آدرس را وارد کنید.',
		'No shipping method has been selected. Please double check your address' => 'روش ارسال انتخاب نشده است. لطفاً آدرس خود را بررسی کنید.',
		'Please read and accept the terms and conditions to proceed with your order.' => 'لطفاً برای ادامه، شرایط و قوانین را بپذیرید.',
		'I have read and agree to the website %s'         => 'شرایط %s را خوانده‌ام و می‌پذیرم',
		'terms and conditions'                            => 'شرایط و قوانین',
		'privacy policy'                                  => 'حریم خصوصی',
		'Your personal data will be used to process your order' => 'اطلاعات شخصی شما برای پردازش سفارش استفاده می‌شود',
		'Your personal data will be used to support your experience throughout this website' => 'اطلاعات شخصی شما برای بهبود تجربه شما در این وب‌سایت استفاده می‌شود',
		'An account is already registered with your email address. <a href="#" class="showlogin">Please log in.</a>' => 'حسابی با این ایمیل ثبت شده است. <a href="#" class="showlogin">لطفاً وارد شوید.</a>',
		'Unfortunately <strong>we do not ship %s</strong>. Please enter an alternative shipping address.' => 'متأسفانه <strong>به %s ارسال نداریم</strong>. لطفاً آدرس دیگری وارد کنید.',
		'We were unable to process your order'            => 'پردازش سفارش شما ممکن نشد',
		'Unable to create order.'                         => 'ایجاد سفارش ممکن نشد.',
		'Invalid payment method.'                         => 'روش پرداخت نامعتبر است.',
		'Invalid payment gateway.'                        => 'درگاه پرداخت نامعتبر است.',
		'Invalid payment method'                          => 'روش پرداخت نامعتبر است',
		'%s is a required field.'                         => 'فیلد %s الزامی است.',
		'%s is not a valid email address.'                => '%s یک ایمیل معتبر نیست.',
		'%s is not a valid phone number.'                 => '%s یک شماره تلفن معتبر نیست.',
		'%s is not a valid postcode / ZIP.'               => '%s یک کد پستی معتبر نیست.',
		'Please enter a valid postcode / ZIP.'            => 'لطفاً کد پستی معتبر وارد کنید.',
		'Please enter a valid Eircode.'                   => 'لطفاً Eircode معتبر وارد کنید.',
		'%1$s is not valid. Please enter one of the following: %2$s' => '%1$s معتبر نیست. یکی از موارد زیر را وارد کنید: %2$s',
		'An error occurred while saving account details: %s' => 'خطا در ذخیره جزئیات حساب: %s',

		// Notices & general.
		'Sorry'                                           => 'متأسفیم',
		'Error:'                                          => 'خطا:',
		'Error: %s'                                       => 'خطا: %s',
		'Dismiss this notice.'                            => 'بستن این پیام.',
		'Dismiss'                                         => 'بستن',
		'Sorry, your session has expired.'                => 'نشست شما منقضی شده است.',
		'Thumbnail image'                                 => 'تصویر بندانگشتی',
		'Download'                                        => 'دانلود',
	);
}

/**
 * WooCommerce plural string translations.
 *
 * @return array<string, string>
 */
function diako_woocommerce_ngettext_map() {
	return array(
		'Showing all %1$d result'                         => 'نمایش %1$d محصول',
		'Showing all %1$d results'                        => 'نمایش %1$d محصول',
		'Showing %1$d&ndash;%2$d of %3$d result'          => 'نمایش %1$d تا %2$d از %3$d محصول',
		'Showing %1$d&ndash;%2$d of %3$d results'        => 'نمایش %1$d تا %2$d از %3$d محصول',
		'Showing %1$d–%2$d of %3$d result'               => 'نمایش %1$d تا %2$d از %3$d محصول',
		'Showing %1$d–%2$d of %3$d results'              => 'نمایش %1$d تا %2$d از %3$d محصول',
		'%1$s review for %2$s'                            => '%1$s نظر برای %2$s',
		'%1$s reviews for %2$s'                           => '%1$s نظر برای %2$s',
		'%s customer review'                              => '%s نظر مشتری',
		'%s customer reviews'                             => '%s نظر مشتری',
		'%s has been added to your cart.'                 => '%s به سبد خرید اضافه شد.',
		'%s have been added to your cart.'                => '%s به سبد خرید اضافه شدند.',
		'%d item from your previous order is currently unavailable and could not be added to your cart.' => '%d محصول از سفارش قبلی شما در دسترس نیست و به سبد اضافه نشد.',
		'%d items from your previous order are currently unavailable and could not be added to your cart.' => '%d محصول از سفارش قبلی شما در دسترس نیستند و به سبد اضافه نشدند.',
	);
}

add_filter(
	'gettext',
	function ( $translation, $text, $domain ) {
		if ( 'woocommerce' !== $domain ) {
			return $translation;
		}

		$map = diako_woocommerce_gettext_map();

		return $map[ $text ] ?? $translation;
	},
	10,
	3
);

add_filter(
	'ngettext',
	function ( $translation, $single, $plural, $number, $domain ) {
		if ( 'woocommerce' !== $domain ) {
			return $translation;
		}

		$map     = diako_woocommerce_ngettext_map();
		$matched = 1 === (int) $number ? $single : $plural;

		return $map[ $matched ] ?? $translation;
	},
	10,
	5
);

add_filter(
	'ngettext_with_context',
	function ( $translation, $single, $plural, $number, $context, $domain ) {
		if ( 'woocommerce' !== $domain || 'with first and last result' !== $context ) {
			return $translation;
		}

		$map     = diako_woocommerce_ngettext_map();
		$matched = 1 === (int) $number ? $single : $plural;

		return $map[ $matched ] ?? $translation;
	},
	10,
	6
);

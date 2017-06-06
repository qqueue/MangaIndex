<?php

// The global composer will load no matter the view, but will only run once per page load
View::composer('*', 'GlobalComposer');

<?php echo "<?php\n"; ?>

class SiteController extends Controller {
	public function actionIndex() {
		$this->render('index');
	}
}
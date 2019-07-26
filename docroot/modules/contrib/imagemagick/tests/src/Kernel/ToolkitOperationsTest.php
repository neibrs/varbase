<?php

namespace Drupal\Tests\imagemagick\Kernel;

use Drupal\imagemagick\ImagemagickExecArguments;
use Drupal\KernelTests\KernelTestBase;

/**
 * Tests for ImageMagick toolkit operations.
 *
 * @group Imagemagick
 */
class ToolkitOperationsTest extends KernelTestBase {

  use ToolkitSetupTrait;

  /**
   * {@inheritdoc}
   */
  public static $modules = ['imagemagick', 'system', 'file_mdm', 'user'];

  /**
   * Create a new image and inspect the arguments.
   *
   * @param string $toolkit_id
   *   The id of the toolkit to set up.
   * @param string $toolkit_config
   *   The config object of the toolkit to set up.
   * @param array $toolkit_settings
   *   The settings of the toolkit to set up.
   *
   * @dataProvider providerToolkitConfiguration
   */
  public function testCreateNewImageArguments($toolkit_id, $toolkit_config, array $toolkit_settings) {
    $this->setUpToolkit($toolkit_id, $toolkit_config, $toolkit_settings);
    $image = $this->imageFactory->get();
    $image->createNew(100, 200);
    $this->assertSame([0], array_keys($image->getToolkit()->arguments()->find('/^./', NULL, ['image_toolkit_operation' => 'create_new'])));
    $this->assertSame([0], array_keys($image->getToolkit()->arguments()->find('/^./', NULL, ['image_toolkit_operation_plugin_id' => 'imagemagick_create_new'])));
    $this->assertSame("-size 100x200 xc:transparent", $image->getToolkit()->arguments()->toString(ImagemagickExecArguments::POST_SOURCE));
  }

  /**
   * Test failures of CreateNew.
   *
   * @param string $toolkit_id
   *   The id of the toolkit to set up.
   * @param string $toolkit_config
   *   The config object of the toolkit to set up.
   * @param array $toolkit_settings
   *   The settings of the toolkit to set up.
   *
   * @dataProvider providerToolkitConfiguration
   */
  public function testCreateNewImageFailures($toolkit_id, $toolkit_config, array $toolkit_settings) {
    $this->setUpToolkit($toolkit_id, $toolkit_config, $toolkit_settings);
    $image = $this->imageFactory->get();
    $image->createNew(-50, 20);
    $this->assertFalse($image->isValid(), 'CreateNew with negative width fails.');
    $image->createNew(50, 20, 'foo');
    $this->assertFalse($image->isValid(), 'CreateNew with invalid extension fails.');
    $image->createNew(50, 20, 'gif', '#foo');
    $this->assertFalse($image->isValid(), 'CreateNew with invalid color hex string fails.');
    $image->createNew(50, 20, 'gif', '#ff0000');
    $this->assertTrue($image->isValid(), 'CreateNew with valid arguments validates the Image.');
  }

  /**
   * Test operations on image with no dimensions.
   *
   * @param string $toolkit_id
   *   The id of the toolkit to set up.
   * @param string $toolkit_config
   *   The config object of the toolkit to set up.
   * @param array $toolkit_settings
   *   The settings of the toolkit to set up.
   *
   * @dataProvider providerToolkitConfiguration
   */
  public function testOperationsOnImageWithNoDimensions($toolkit_id, $toolkit_config, array $toolkit_settings) {
    $this->setUpToolkit($toolkit_id, $toolkit_config, $toolkit_settings);
    $image = $this->imageFactory->get();
    $image->createNew(100, 200);
    $this->assertSame(100, $image->getWidth());
    $this->assertsame(200, $image->getHeight());
    $image->getToolkit()->setWidth(NULL);
    $image->getToolkit()->setHeight(NULL);
    $this->assertNull($image->getWidth());
    $this->assertNull($image->getHeight());
    $image->rotate(5);
    $this->assertNull($image->getWidth());
    $this->assertNull($image->getHeight());
    $image->crop(10, 10, 20, 20);
    $this->assertNull($image->getWidth());
    $this->assertNull($image->getHeight());
    $image->scaleAndCrop(10, 10);
    $this->assertNull($image->getWidth());
    $this->assertNull($image->getHeight());
    $image->scale(5);
    $this->assertNull($image->getWidth());
    $this->assertNull($image->getHeight());
    $image->resize(50, 100);
    $this->assertSame(50, $image->getWidth());
    $this->assertsame(100, $image->getHeight());
    if (substr(PHP_OS, 0, 3) === 'WIN') {
      $this->assertSame("-size 100x200 xc:transparent -background \"transparent\" -rotate 5 +repage -resize 50x100!", $image->getToolkit()->arguments()->toString(ImagemagickExecArguments::POST_SOURCE));
    }
    else {
      $this->assertSame("-size 100x200 xc:transparent -background 'transparent' -rotate 5 +repage -resize 50x100!", $image->getToolkit()->arguments()->toString(ImagemagickExecArguments::POST_SOURCE));
    }
  }

}

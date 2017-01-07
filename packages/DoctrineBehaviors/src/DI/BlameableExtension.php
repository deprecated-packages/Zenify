<?php

declare(strict_types=1);

namespace Zenify\DoctrineBehaviors\DI;

use Kdyby\Events\DI\EventsExtension;
use Knp\DoctrineBehaviors\Model\Blameable\Blameable;
use Knp\DoctrineBehaviors\ORM\Blameable\BlameableSubscriber;
use Nette\Utils\AssertionException;
use Nette\Utils\Validators;
use Zenify\DoctrineBehaviors\Blameable\UserCallable;


final class BlameableExtension extends AbstractBehaviorExtension
{

	/**
	 * @var array
	 */
	private $defaults = [
		'isRecursive' => TRUE,
		'trait' => Blameable::class,
		'userCallable' => UserCallable::class,
		'userEntity' => NULL
	];


	public function loadConfiguration()
	{
		$config = $this->validateConfig($this->defaults);
		$this->validateConfigTypes($config);
		$builder = $this->getContainerBuilder();

		$userCallable = $this->buildDefinitionFromCallable($config['userCallable']);

		$builder->addDefinition($this->prefix('listener'))
			->setClass(BlameableSubscriber::class, [
				'@' . $this->getClassAnalyzer()->getClass(),
				$config['isRecursive'],
				$config['trait'],
				'@' . $userCallable->getClass(),
				$config['userEntity']
			])
			->setAutowired(FALSE)
			->addTag(EventsExtension::TAG_SUBSCRIBER);
	}


	/**
	 * @throws AssertionException
	 */
	private function validateConfigTypes(array $config)
	{
		Validators::assertField($config, 'isRecursive', 'bool');
		Validators::assertField($config, 'trait', 'type');
		Validators::assertField($config, 'userCallable', 'string');
		Validators::assertField($config, 'userEntity', 'null|string');
	}

}

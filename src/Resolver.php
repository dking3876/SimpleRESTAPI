<?php
namespace SimpleRESTAPI;
/**
 * Class to instantiate class objects with DI
 * class arguments must be typehinted 
 */
class Resolver {

	/**
 	* Build an instance of the given class
	* 
 	* @param string $class
 	* @return mixed
 	*
 	* @throws Exception
 	*/
	public function resolve($class)
	{
		$reflector = new \ReflectionClass($class);

		if( ! $reflector->isInstantiable())
 		{
 			throw new \Exception("[$class] is not instantiable");
 		}
		
 		$constructor = $reflector->getConstructor();
		
 		if(is_null($constructor))
 		{
 			return new $class;
 		}
		
 		$parameters = $constructor->getParameters();
 		$dependencies = $this->getDependencies($parameters);
		
 		return $reflector->newInstanceArgs($dependencies);
	}
	
	/**
	 * Build up a list of dependencies for a given methods parameters
	 *
	 * @param array $parameters
	 * @return array
	 */
	public function getDependencies($parameters)
	{
		$dependencies = array();
		
		foreach($parameters as $parameter)
		{
			$dependency = $parameter->getClass();
			
			if(is_null($dependency))
			{
				$dependencies[] = $this->resolveNonClass($parameter);
			}
			else
			{
				$dependencies[] = $this->resolve($dependency->name);
			}
		}
		
		return $dependencies;
	}
	
	/**
	 * Determine what to do with a non-class value
	 *
	 * @param ReflectionParameter $parameter
	 * @return mixed
	 *
	 * @throws Exception
	 */
	public function resolveNonClass(ReflectionParameter $parameter)
	{
		if($parameter->isDefaultValueAvailable())
		{
			return $parameter->getDefaultValue();
		}
		
		throw new Exception("Erm.. Cannot resolve the unkown!?");
	}
	/**
	 * Check method for parameter Type check for model object interfaces
	 *
	 * @param string $class
	 * @param string $method
	 * @return string | nulls
	 */
	static function resolveMethodParameters($class, $method){
		$reflector = new \ReflectionClass($class);

		if( ! $reflector->isInstantiable())
 		{
 			throw new \Exception("[$class] is not instantiable");
 		}

		 $method = $reflector->getMethod($method);
		 $parameters = $method->getParameters();
		 foreach($parameters as $parameter){
			 $parameter_class = $parameter->getClass();
			 if(!is_null($parameter_class)){
				 return $parameter_class;
			 }
		 }
		 return null;
	}
}
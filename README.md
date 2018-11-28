# Install

		$ git clone https://github.com/rubencougil/review-heroes.git
		$ cd review-heroes
		$ docker-compose build
		$ docker-compose up -d
		
# Deploy to Prod
Using [Deployer](http://deployer.org)

		$ cd review-heroes/app
		$ ./vendor/bin/dep deploy [--revision=[commit|branch|tag]]
		
# Ngrok

[Ngrok](https://ngrok.com/) dashboard at http://localhost:4040

# Test

Open url http://localhost:80 

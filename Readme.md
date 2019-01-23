[![Latest Stable Version](https://poser.pugx.org/dl/assetsource-unsplash/v/stable)](https://packagist.org/packages/dl/assetsource-unsplash) [![Total Downloads](https://poser.pugx.org/dl/assetsource-unsplash/downloads)](https://packagist.org/packages/dl/assetsource-unsplash) [![License](https://poser.pugx.org/dl/assetsource-unsplash/license)](https://packagist.org/packages/dl/assetsource-unsplash)

# Unsplash Asset Source

## Installation

Install the package via composer

    composer require dl/assetsource-unsplash

## How to use it

1. Read the [Unsplash API guidelines](https://medium.com/unsplash/unsplash-api-guidelines-28e0216e6daa) carefully!
2. Register on [https://unsplash.com/developers](https://unsplash.com/developers) and get your API key.
3. Configure the API key in the settings

Please take care of the correct attribution of used photos in the frontend. 

![Media Browser view](https://user-images.githubusercontent.com/642226/40078557-3bff9fee-5885-11e8-9d84-de031e1b8620.png)

## Configuration

Since Neos 4.2 a copyright notice is automatically generated and stored with the asset. The copyright notice
template can be adjusted using the `copyRightNoticeTemplate` configuration value.

The following data can be used:
    
* user.id
* user.updated_at
* user.username
* user.name
* user.first_name
* user.last_name
* user.twitter_username
* user.portfolio_url
* user.bio
* user.location
* user.links
* user.profile_image
* user.instagram_username
* user.total_collections
* user.total_likes
* user.total_photos
* user.accepted_tos


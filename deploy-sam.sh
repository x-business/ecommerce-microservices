#!/bin/bash

# AWS SAM Deployment Script

set -e

ENVIRONMENT=${1:-dev}
REGION=${2:-us-east-1}
STACK_NAME="ecommerce-sam-${ENVIRONMENT}"

echo "Deploying E-Commerce Store serverless API to AWS..."
echo "Environment: ${ENVIRONMENT}"
echo "Region: ${REGION}"

# Build SAM application
echo "Building SAM application..."
sam build

# Deploy SAM application
echo "Deploying SAM application..."
sam deploy \
    --stack-name ${STACK_NAME} \
    --region ${REGION} \
    --capabilities CAPABILITY_IAM \
    --parameter-overrides \
        Environment=${ENVIRONMENT} \
    --confirm-changeset

# Get API URL
API_URL=$(aws cloudformation describe-stacks \
    --stack-name ${STACK_NAME} \
    --region ${REGION} \
    --query 'Stacks[0].Outputs[?OutputKey==`ApiUrl`].OutputValue' \
    --output text)

echo "Serverless API deployed successfully!"
echo "API URL: ${API_URL}"

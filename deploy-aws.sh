#!/bin/bash

# AWS CloudFormation Deployment Script

set -e

ENVIRONMENT=${1:-dev}
REGION=${2:-us-east-1}
STACK_PREFIX="ecommerce-${ENVIRONMENT}"

echo "Deploying E-Commerce Store infrastructure to AWS..."
echo "Environment: ${ENVIRONMENT}"
echo "Region: ${REGION}"

# Deploy Database Stack
echo "Deploying database stack..."
aws cloudformation deploy \
    --template-file aws/cloudformation/database.yml \
    --stack-name "${STACK_PREFIX}-database" \
    --parameter-overrides Environment=${ENVIRONMENT} \
    --region ${REGION} \
    --capabilities CAPABILITY_IAM

# Get database endpoint
DB_ENDPOINT=$(aws cloudformation describe-stacks \
    --stack-name "${STACK_PREFIX}-database" \
    --region ${REGION} \
    --query 'Stacks[0].Outputs[?OutputKey==`DatabaseEndpoint`].OutputValue' \
    --output text)

echo "Database endpoint: ${DB_ENDPOINT}"

# Deploy Application Stack
echo "Deploying application stack..."
aws cloudformation deploy \
    --template-file aws/cloudformation/application.yml \
    --stack-name "${STACK_PREFIX}-application" \
    --parameter-overrides Environment=${ENVIRONMENT} \
    --region ${REGION} \
    --capabilities CAPABILITY_IAM

# Get Load Balancer URL
ALB_URL=$(aws cloudformation describe-stacks \
    --stack-name "${STACK_PREFIX}-application" \
    --region ${REGION} \
    --query 'Stacks[0].Outputs[?OutputKey==`LoadBalancerURL`].OutputValue' \
    --output text)

echo "Application deployed successfully!"
echo "Load Balancer URL: ${ALB_URL}"
echo "Database Endpoint: ${DB_ENDPOINT}"
